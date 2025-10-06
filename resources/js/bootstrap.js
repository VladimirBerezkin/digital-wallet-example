import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { isLocalEnv } from './utils/env.js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

// Setup Pusher & Echo for real-time broadcasting
window.Pusher = Pusher;

// Initialize Echo with custom authorizer for Sanctum
window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    encrypted: true,
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                axios.post('/api/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    callback(false, response.data);
                })
                .catch(error => {
                    callback(true, error);
                });
            }
        };
    },
    enabledTransports: ['ws', 'wss'],
    disabledTransports: [],
});

// Pusher connection debugging (only in local environment)
if (isLocalEnv()) {
    window.Echo.connector.pusher.connection.bind('connected', function() {
        console.log('Pusher connected successfully');
    });

    window.Echo.connector.pusher.connection.bind('disconnected', function() {
        console.log('Pusher disconnected');
    });

    window.Echo.connector.pusher.connection.bind('error', function(error) {
        console.error('Pusher connection error:', error);
    });

    // Add debugging for Pusher channels
    window.Echo.connector.pusher.bind('pusher:subscription_succeeded', function(data) {
        console.log('Pusher subscription succeeded:', data);
    });

    window.Echo.connector.pusher.bind('pusher:subscription_error', function(data) {
        console.error('Pusher subscription error:', data);
    });
}
