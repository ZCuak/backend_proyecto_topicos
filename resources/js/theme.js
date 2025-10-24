document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('themeToggle');
    const circle = document.getElementById('themeCircle');
    const root = document.documentElement;

    // Estado inicial
    if (localStorage.theme === 'dark' ||
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }

    // Cambiar tema
    toggle.addEventListener('click', () => {
        root.classList.toggle('dark');
        const isDark = root.classList.contains('dark');
        localStorage.theme = isDark ? 'dark' : 'light';

        // Animación del círculo
        circle.classList.toggle('translate-x-1');
        circle.classList.toggle('translate-x-7');
    });
});