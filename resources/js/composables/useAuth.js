import { ref, onMounted } from "vue";
import axios from "axios";

// Create singleton instance
let authInstance = null;

export function useAuth() {
    if (authInstance) {
        return authInstance;
    }

    const user = ref(null);
    const loading = ref(false);
    const error = ref(null);

    // Initialize user from localStorage on composable creation
    const initializeUser = () => {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            try {
                const parsedUser = JSON.parse(storedUser);
                user.value = parsedUser;
            } catch (e) {
                console.warn('Failed to parse stored user data:', e);
                localStorage.removeItem('user');
                user.value = null;
            }
        } else {
            user.value = null;
        }
    };

    // Initialize user state
    initializeUser();

    const login = async (email, password) => {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await axios.post("/api/auth/login", {
                email,
                password,
            });
            user.value = data.user;
            // Persist user data to localStorage
            localStorage.setItem('user', JSON.stringify(data.user));
            return { success: true };
        } catch (err) {
            error.value = err.response?.data?.message || "Login failed";
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            await axios.post("/api/auth/logout");
            user.value = null;
            // Clear user data from localStorage
            localStorage.removeItem('user');
            return { success: true };
        } catch (err) {
            // Even if logout fails on server, clear local state
            user.value = null;
            localStorage.removeItem('user');
            return { success: false };
        }
    };

    const fetchUser = async () => {
        try {
            const { data } = await axios.get("/api/auth/user");
            user.value = data;
            // Update localStorage with fresh user data
            localStorage.setItem('user', JSON.stringify(data));
            return { success: true };
        } catch (err) {
            // Only clear user state if it's a 401 (unauthorized) error
            if (err.response?.status === 401) {
                user.value = null;
                localStorage.removeItem('user');
            }
            return { success: false };
        }
    };

    const isAuthenticated = () => {
        return user.value !== null && user.value !== undefined;
    };

    authInstance = { user, loading, error, login, logout, fetchUser, isAuthenticated };
    return authInstance;
}
