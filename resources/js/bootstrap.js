import axios from "axios";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

// Fetch CSRF token on initialization
axios.get('/api/csrf-token').then(response => {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrf_token;
}).catch(error => {
    console.warn('Failed to fetch CSRF token:', error);
});

// Setup Pusher & Echo for real-time broadcasting
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
