import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',       // Hace que el servidor esté accesible desde fuera del contenedor
        port: 5173,            // Asegúrate que este puerto esté expuesto
        hmr: {
            host: 'localhost', // O la IP de tu WSL (ver más abajo)
        }
    }
});
