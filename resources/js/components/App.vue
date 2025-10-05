<script setup>
import { ref, onMounted } from "vue";
import { useAuth } from "@/composables/useAuth";
import LoginPage from "./LoginPage.vue";
import WalletDashboard from "./WalletDashboard.vue";

const { user, fetchUser, logout } = useAuth();
const isLoading = ref(true);

onMounted(async () => {
    await fetchUser();
    isLoading.value = false;
});

const handleLoginSuccess = async () => {
    await fetchUser();
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
        <LoginPage v-if="!user" @login-success="handleLoginSuccess" />
        <WalletDashboard v-else :user="user" @logout="handleLogout" />
    </div>
</template>
