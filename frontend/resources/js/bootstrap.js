import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import Swal from 'sweetalert2';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Interceptor de Request (Token)
window.axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

/**
 * INTERCEPTOR ÚNICO DE RESPOSTA
 * Resolve: Logout automático (401) e Aviso de Serviço Offline (503)
 */
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response ? error.response.status : null;

        if (status === 401) {
            // Token inválido ou expirado - Limpa tudo e desloga
            console.warn('Sessão expirada. Limpando dados...');
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }

        if (status === 503) {
            // Serviço de Autenticação Down (Fail-fast do Middleware)
            Swal.fire({
                title: 'Conexão Perdida',
                text: 'O serviço de autenticação está temporariamente indisponível. A operação foi bloqueada por segurança.',
                icon: 'warning',
                confirmButtonColor: '#4f46e5'
            });
        }

        return Promise.reject(error);
    }
);

// Configuração do Echo...
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
