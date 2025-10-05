import { ref } from "vue";
import axios from "axios";

export function useAuth() {
    const user = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const login = async (email, password) => {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await axios.post("/api/auth/login", {
                email,
                password,
            });
            user.value = data.user;
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
            return { success: true };
        } catch (err) {
            console.error("Logout failed:", err);
            return { success: false };
        }
    };

    const fetchUser = async () => {
        try {
            const { data } = await axios.get("/api/auth/user");
            user.value = data;
            return { success: true };
        } catch (err) {
            user.value = null;
            return { success: false };
        }
    };

    return { user, loading, error, login, logout, fetchUser };
}
