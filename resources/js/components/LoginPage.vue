<script setup>
import { ref } from "vue";
import { useAuth } from "@/composables/useAuth";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Password from "primevue/password";
import Button from "primevue/button";
import Message from "primevue/message";

const emit = defineEmits(["login-success"]);

const { login, loading, error } = useAuth();

const email = ref("");
const password = ref("");

const testUsers = [
    {
        email: "alice@example.com",
        name: "Alice Johnson",
        balance: "$1,000.00",
    },
    { email: "bob@example.com", name: "Bob Smith", balance: "$500.00" },
    { email: "charlie@example.com", name: "Charlie Brown", balance: "$0.00" },
    {
        email: "diana@example.com",
        name: "Diana Prince",
        balance: "$10,000.00",
    },
];

const handleLogin = async () => {
    const result = await login(email.value, password.value);
    if (result.success) {
        emit("login-success");
    }
};

const quickLogin = (userEmail) => {
    email.value = userEmail;
    password.value = "password";
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50 p-6">
        <div class="w-full max-w-md">
            <Card class="shadow-lg">
                <template #title>
                    <div class="px-6 pt-6 text-center">
                        <i class="pi pi-wallet mb-3 text-5xl text-primary"></i>
                        <h1 class="text-3xl font-bold text-gray-800">
                            Digital Wallet
                        </h1>
                        <p class="mt-2 text-sm text-gray-500">
                            Sign in to manage your wallet
                        </p>
                    </div>
                </template>

                <template #content>
                    <div class="px-6 pb-6">
                        <Message
                            v-if="error"
                            severity="error"
                            :closable="false"
                            class="mb-6"
                        >
                            {{ error }}
                        </Message>

                        <form
                            @submit.prevent="handleLogin"
                            class="flex flex-col gap-5"
                        >
                            <div>
                                <label
                                    for="email"
                                    class="mb-2 block text-sm font-semibold text-gray-700"
                                    >Email Address</label
                                >
                                <InputText
                                    id="email"
                                    v-model="email"
                                    type="email"
                                    placeholder="Enter your email"
                                    class="w-full"
                                    :disabled="loading"
                                    size="large"
                                />
                            </div>

                            <div>
                                <label
                                    for="password"
                                    class="mb-2 block text-sm font-semibold text-gray-700"
                                    >Password</label
                                >
                                <Password
                                    id="password"
                                    v-model="password"
                                    placeholder="Enter your password"
                                    :feedback="false"
                                    toggleMask
                                    class="w-full"
                                    :disabled="loading"
                                    inputClass="w-full"
                                />
                            </div>

                            <Button
                                type="submit"
                                label="Login"
                                icon="pi pi-sign-in"
                                :loading="loading"
                                class="mt-2 w-full"
                                size="large"
                            />
                        </form>

                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <p
                                class="mb-4 text-center text-sm font-semibold text-gray-700"
                            >
                                Quick Login - Test Users
                            </p>
                            <p class="mb-4 text-center text-xs text-gray-500">
                                Password: "password" for all users
                            </p>
                            <div class="flex flex-col gap-3">
                                <Button
                                    v-for="user in testUsers"
                                    :key="user.email"
                                    severity="secondary"
                                    outlined
                                    @click="quickLogin(user.email)"
                                    class="justify-between px-4 py-3"
                                >
                                    <template #default>
                                        <div
                                            class="flex w-full items-center justify-between"
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <i
                                                    class="pi pi-user text-lg text-gray-600"
                                                ></i>
                                                <div class="text-left">
                                                    <div
                                                        class="text-sm font-semibold text-gray-800"
                                                    >
                                                        {{ user.name }}
                                                    </div>
                                                    <div
                                                        class="text-xs text-gray-500"
                                                    >
                                                        {{ user.email }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="text-sm font-bold text-green-600"
                                            >
                                                {{ user.balance }}
                                            </div>
                                        </div>
                                    </template>
                                </Button>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
