import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { initReveal } from './reveal';

Alpine.plugin(collapse);
window.Alpine = Alpine;

document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
    if (document.documentElement.classList.contains('reveal-ready')) initReveal();
});
