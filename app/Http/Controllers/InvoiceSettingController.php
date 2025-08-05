<?php

namespace App\Http\Controllers;

use App\Models\InvoiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class InvoiceSettingController extends Controller
{
    /**
     * Display the invoice settings page.
     */
    public function index()
    {
        $settings = InvoiceSetting::orderBy('key')->get()->keyBy('key');
        
        // Group settings by category for better organization
        $groupedSettings = [
            'company' => [
                'title' => 'Company Information',
                'icon' => 'building',
                'settings' => $settings->filter(fn($setting) => str_starts_with($setting->key, 'company_'))
            ],
            'invoice' => [
                'title' => 'Invoice Configuration',
                'icon' => 'file-text',
                'settings' => $settings->filter(fn($setting) => str_starts_with($setting->key, 'invoice_'))
            ],
            'defaults' => [
                'title' => 'Default Settings',
                'icon' => 'settings',
                'settings' => $settings->filter(fn($setting) => str_starts_with($setting->key, 'default_'))
            ],
            'automation' => [
                'title' => 'Automation Settings',
                'icon' => 'zap',
                'settings' => $settings->filter(fn($setting) => str_starts_with($setting->key, 'auto_'))
            ],
            'other' => [
                'title' => 'Other Settings',
                'icon' => 'more-horizontal',
                'settings' => $settings->reject(function($setting) {
                    return str_starts_with($setting->key, 'company_') ||
                           str_starts_with($setting->key, 'invoice_') ||
                           str_starts_with($setting->key, 'default_') ||
                           str_starts_with($setting->key, 'auto_');
                })
            ]
        ];

        // Remove empty groups
        $groupedSettings = collect($groupedSettings)->filter(fn($group) => $group['settings']->isNotEmpty());

        return view('invoice-settings.index', compact('groupedSettings'));
    }

    /**
     * Show the form for creating a new setting.
     */
    public function create()
    {
        $settingTypes = [
            'text' => 'Text',
            'number' => 'Number',
            'boolean' => 'Boolean (True/False)',
            'email' => 'Email',
            'url' => 'URL',
            'textarea' => 'Long Text',
            'select' => 'Select Option',
            'json' => 'JSON Data',
        ];

        return view('invoice-settings.create', compact('settingTypes'));
    }

    /**
     * Store a newly created setting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:invoice_settings,key|max:255|regex:/^[a-z_]+$/',
            'value' => 'nullable|string',
            'type' => ['required', Rule::in(['text', 'number', 'boolean', 'email', 'url', 'textarea', 'select', 'json'])],
            'description' => 'nullable|string',
        ]);

        // Validate value based on type
        $this->validateSettingValue($validated['value'], $validated['type']);

        try {
            $setting = InvoiceSetting::create($validated);
            
            // Clear settings cache
            $this->clearSettingsCache();

            return redirect()->route('invoice-settings.index')
                ->with('success', 'Setting created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create setting: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing a setting.
     */
    public function edit(InvoiceSetting $invoiceSetting)
    {
        $settingTypes = [
            'text' => 'Text',
            'number' => 'Number',
            'boolean' => 'Boolean (True/False)',
            'email' => 'Email',
            'url' => 'URL',
            'textarea' => 'Long Text',
            'select' => 'Select Option',
            'json' => 'JSON Data',
        ];

        return view('invoice-settings.edit', compact('invoiceSetting', 'settingTypes'));
    }

    /**
     * Update the specified setting.
     */
    public function update(Request $request, InvoiceSetting $invoiceSetting)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255', 'regex:/^[a-z_]+$/', 
                     Rule::unique('invoice_settings')->ignore($invoiceSetting->id)],
            'value' => 'nullable|string',
            'type' => ['required', Rule::in(['text', 'number', 'boolean', 'email', 'url', 'textarea', 'select', 'json'])],
            'description' => 'nullable|string',
        ]);

        // Validate value based on type
        $this->validateSettingValue($validated['value'], $validated['type']);

        try {
            $invoiceSetting->update($validated);
            
            // Clear settings cache
            $this->clearSettingsCache();

            return redirect()->route('invoice-settings.index')
                ->with('success', 'Setting updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update setting: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified setting.
     */
    public function destroy(InvoiceSetting $invoiceSetting)
    {
        // Prevent deletion of critical system settings
        $protectedSettings = [
            'company_name', 'company_address', 'company_email', 'company_phone',
            'default_currency', 'default_payment_terms', 'invoice_prefix'
        ];

        if (in_array($invoiceSetting->key, $protectedSettings)) {
            return back()->with('error', 'Cannot delete protected system setting.');
        }

        try {
            $invoiceSetting->delete();
            
            // Clear settings cache
            $this->clearSettingsCache();

            return redirect()->route('invoice-settings.index')
                ->with('success', 'Setting deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete setting: ' . $e->getMessage());
        }
    }

    /**
     * Update multiple settings at once.
     */
    public function updateBulk(Request $request)
    {
        $settings = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ])['settings'];

        DB::beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                $setting = InvoiceSetting::where('key', $key)->first();
                
                if ($setting) {
                    // Validate value based on setting type
                    $this->validateSettingValue($value, $setting->type);
                    
                    $setting->update(['value' => $value]);
                }
            }

            DB::commit();
            
            // Clear settings cache
            $this->clearSettingsCache();

            return back()->with('success', 'Settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Reset settings to default values.
     */
    public function reset()
    {
        DB::beginTransaction();
        try {
            // Get default settings from seeder
            $defaultSettings = [
                'company_name' => 'Your Company Name',
                'company_address' => 'Your Company Address',
                'company_phone' => '+62xxx-xxxx-xxxx',
                'company_email' => 'info@company.com',
                'default_payment_terms' => '14',
                'default_currency' => 'IDR',
                'auto_send_invoice' => 'false',
                'invoice_prefix' => 'INV',
            ];

            foreach ($defaultSettings as $key => $value) {
                InvoiceSetting::where('key', $key)->update(['value' => $value]);
            }

            DB::commit();
            
            // Clear settings cache
            $this->clearSettingsCache();

            return back()->with('success', 'Settings reset to default values successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    /**
     * Export settings as JSON.
     */
    public function export()
    {
        $settings = InvoiceSetting::all(['key', 'value', 'type', 'description']);
        
        $filename = 'invoice-settings-' . now()->format('Y-m-d-H-i-s') . '.json';
        
        return response()->json($settings, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Import settings from JSON.
     */
    public function import(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json',
            'overwrite_existing' => 'boolean',
        ]);

        try {
            $file = $request->file('settings_file');
            $content = file_get_contents($file->getRealPath());
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON file.');
            }

            DB::beginTransaction();

            $imported = 0;
            $skipped = 0;

            foreach ($settings as $settingData) {
                if (!isset($settingData['key']) || !isset($settingData['value'])) {
                    continue;
                }

                $existing = InvoiceSetting::where('key', $settingData['key'])->first();

                if ($existing && !$request->boolean('overwrite_existing')) {
                    $skipped++;
                    continue;
                }

                InvoiceSetting::updateOrCreate(
                    ['key' => $settingData['key']],
                    [
                        'value' => $settingData['value'],
                        'type' => $settingData['type'] ?? 'text',
                        'description' => $settingData['description'] ?? null,
                    ]
                );

                $imported++;
            }

            DB::commit();
            
            // Clear settings cache
            $this->clearSettingsCache();

            return back()->with('success', 
                "Settings imported successfully. {$imported} imported, {$skipped} skipped.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to import settings: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific setting value.
     */
    public function getSetting($key)
    {
        $setting = $this->getCachedSetting($key);
        
        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        return response()->json([
            'key' => $setting->key,
            'value' => $this->castSettingValue($setting->value, $setting->type),
            'type' => $setting->type,
        ]);
    }

    /**
     * Update a specific setting value.
     */
    public function updateSetting(Request $request, $key)
    {
        $setting = InvoiceSetting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        try {
            // Validate value based on setting type
            $this->validateSettingValue($validated['value'], $setting->type);
            
            $setting->update(['value' => $validated['value']]);
            
            // Clear settings cache
            $this->clearSettingsCache();

            return response()->json([
                'message' => 'Setting updated successfully',
                'key' => $setting->key,
                'value' => $this->castSettingValue($setting->value, $setting->type),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get all settings as key-value pairs.
     */
    public function getAllSettings()
    {
        $settings = Cache::remember('invoice_settings_all', 3600, function () {
            return InvoiceSetting::pluck('value', 'key');
        });

        return response()->json($settings);
    }

    /**
     * Validate setting value based on its type.
     */
    private function validateSettingValue($value, $type)
    {
        if ($value === null || $value === '') {
            return; // Allow empty values
        }

        switch ($type) {
            case 'number':
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException('Value must be a number.');
                }
                break;

            case 'boolean':
                if (!in_array(strtolower($value), ['true', 'false', '1', '0'])) {
                    throw new \InvalidArgumentException('Value must be true or false.');
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException('Value must be a valid email address.');
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    throw new \InvalidArgumentException('Value must be a valid URL.');
                }
                break;

            case 'json':
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException('Value must be valid JSON.');
                }
                break;
        }
    }

    /**
     * Cast setting value to appropriate type.
     */
    private function castSettingValue($value, $type)
    {
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (float) $value : $value;
            
            case 'boolean':
                return in_array(strtolower($value), ['true', '1']) ? true : false;
            
            case 'json':
                return json_decode($value, true);
            
            default:
                return $value;
        }
    }

    /**
     * Get cached setting.
     */
    private function getCachedSetting($key)
    {
        return Cache::remember("invoice_setting_{$key}", 3600, function () use ($key) {
            return InvoiceSetting::where('key', $key)->first();
        });
    }

    /**
     * Clear settings cache.
     */
    private function clearSettingsCache()
    {
        Cache::forget('invoice_settings_all');
        
        // Clear individual setting caches
        $settings = InvoiceSetting::pluck('key');
        foreach ($settings as $key) {
            Cache::forget("invoice_setting_{$key}");
        }
    }

    /**
     * Helper method to get setting value (for use in other parts of the application).
     */
    public static function get($key, $default = null)
    {
        $setting = Cache::remember("invoice_setting_{$key}", 3600, function () use ($key) {
            return InvoiceSetting::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        // Cast value based on type
        switch ($setting->type) {
            case 'number':
                return is_numeric($setting->value) ? (float) $setting->value : $default;
            
            case 'boolean':
                return in_array(strtolower($setting->value), ['true', '1']);
            
            case 'json':
                $decoded = json_decode($setting->value, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
            
            default:
                return $setting->value ?? $default;
        }
    }

    /**
     * Helper method to set setting value.
     */
    public static function set($key, $value)
    {
        $setting = InvoiceSetting::where('key', $key)->first();
        
        if ($setting) {
            $setting->update(['value' => $value]);
        } else {
            InvoiceSetting::create([
                'key' => $key,
                'value' => $value,
                'type' => 'text',
                'description' => null,
            ]);
        }

        // Clear cache
        Cache::forget("invoice_setting_{$key}");
        Cache::forget('invoice_settings_all');
    }
}