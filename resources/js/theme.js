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

});