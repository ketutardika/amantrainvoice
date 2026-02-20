@if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
    <x-filament-panels::theme-switcher />
@endif
