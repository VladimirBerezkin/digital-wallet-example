<script setup>
import { ref, onMounted } from "vue";
import { useAuth } from "@/composables/useAuth";
import LoginPage from "./LoginPage.vue";
import WalletDashboard from "./WalletDashboard.vue";

const { user, fetchUser, logout, isAuthenticated, clearUser } = useAuth();
const isLoading = ref(true);

onMounted(async () => {
    // Always try to fetch the current user to ensure authentication state is correct
    if (user.value) {
        // If we have a user in localStorage, validate it with the server
        const result = await fetchUser();
        if (!result.success) {
            // If validation failed, user will be cleared by fetchUser
        }
    } else {
        // Only try to get user from session if we have a token
        const token = localStorage.getItem('token');
        if (token) {
            await fetchUser();
        }
    }
    isLoading.value = false;
});

const handleLogout = async () => {
    await logout();
};
</script>

<template>
    <div v-if="isLoading" class="flex min-h-screen items-center justify-center">
        <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
    </div>
    <div v-else>
        <LoginPage v-if="!isAuthenticated" />
        <WalletDashboard v-else :user="user" @logout="handleLogout" />
    </div>
</template>
