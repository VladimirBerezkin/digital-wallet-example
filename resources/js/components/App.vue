<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useAuth } from "@/composables/useAuth";
import LoginPage from "./LoginPage.vue";
import WalletDashboard from "./WalletDashboard.vue";

const { user, fetchUser, logout, isAuthenticated } = useAuth();
const isLoading = ref(true);

// Create a reactive ref for authentication state
const isUserAuthenticated = ref(false);

// Watch for changes in user state and update authentication state
watch(user, (newUser) => {
    isUserAuthenticated.value = newUser !== null && newUser !== undefined;
}, { immediate: true });

onMounted(async () => {
    // If we have a user in localStorage, try to validate it with the server
    // If no user in localStorage, we'll show the login form immediately
    if (user.value !== null && user.value !== undefined) {
        // Validate the stored user with the server
        const result = await fetchUser();
        // If validation fails, the user state will be cleared by fetchUser
    }
    isLoading.value = false;
});

const handleLoginSuccess = async () => {
    // No need to call fetchUser again as login already updates the user state
    // The user state is already updated in the useAuth composable
};

const handleLogout = async () => {
    await logout();
};
</script>

<template>
    <div v-if="isLoading" class="flex min-h-screen items-center justify-center">
        <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
    </div>
    <div v-else>
        <LoginPage v-if="!isUserAuthenticated" @login-success="handleLoginSuccess" />
        <WalletDashboard v-else :user="user" @logout="handleLogout" />
    </div>
</template>
