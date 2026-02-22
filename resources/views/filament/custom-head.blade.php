@vite(['resources/css/app.css'])

{{-- Default to light mode if no theme preference is stored yet --}}
<script>
    if (!localStorage.getItem('theme')) {
        localStorage.setItem('theme', 'light');
    }
</script>
