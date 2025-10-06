<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useWallet } from "@/composables/useWallet";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Toast from "primevue/toast";
import BalanceCard from "./BalanceCard.vue";
import TestUsersCard from "./TestUsersCard.vue";
import TransferForm from "./TransferForm.vue";
import TransactionHistory from "./TransactionHistory.vue";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["logout"]);

const {
    balance,
    transactions,
    loading,
    transfer,
    fetchTransactions,
    listenForUpdates,
    stopListening,
} = useWallet();
const toast = useToast();

const receiverId = ref(null);
const submitting = ref(false);
const transferFormRef = ref(null);
const validationErrors = ref({});

onMounted(async () => {
    await fetchTransactions();
    listenForUpdates(props.user.id);
});

onUnmounted(() => {
    stopListening(props.user.id);
});

const handleSelectUser = (userId) => {
    receiverId.value = userId;
    // Clear validation errors when user selects a new receiver
    validationErrors.value = {};
};

const handleTransferSubmit = async (transferData) => {
    submitting.value = true;
    validationErrors.value = {}; // Clear previous errors

    const result = await transfer(
        transferData.receiverId,
        transferData.amount,
        transferData.description,
    );

    if (result.success) {
        toast.add({
            severity: "success",
            summary: "Success",
            detail: "Transfer completed successfully",
            life: 3000,
        });
        receiverId.value = null;
        if (transferFormRef.value) {
            transferFormRef.value.clear();
        }
    } else {
        // Check if we have validation errors
        if (result.errors) {
            validationErrors.value = result.errors;
            toast.add({
                severity: "error",
                summary: "Validation Error",
                detail: "Please check the form for errors",
                life: 5000,
            });
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: result.error || "Transfer failed",
                life: 5000,
            });
        }
    }

    submitting.value = false;
};

const handleClearForm = () => {
    receiverId.value = null;
    validationErrors.value = {};
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-6">
        <Toast position="top-right" />

        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div
                class="mb-8 flex flex-col gap-4 rounded-xl bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div class="flex items-center gap-3">
                        <i class="pi pi-wallet text-3xl text-primary"></i>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">
                                Digital Wallet
                            </h1>
                            <p class="mt-1 text-sm text-gray-500">
                                Welcome back,
                                <span class="font-semibold text-gray-700">{{
                                    user.name
                                }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <Button
                    label="Logout"
                    icon="pi pi-sign-out"
                    severity="secondary"
                    outlined
                    @click="emit('logout')"
                    class="self-start sm:self-auto"
                />
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                <!-- Balance Card -->
                <BalanceCard :balance="balance" class="lg:col-span-1" />

                <!-- Test Users Card -->
                <TestUsersCard
                    :current-user-id="user.id"
                    :selected-receiver-id="receiverId"
                    :disabled="submitting"
                    @select-user="handleSelectUser"
                    class="lg:col-span-1"
                />

                <!-- Transfer Form -->
                <TransferForm
                    ref="transferFormRef"
                    :receiver-id="receiverId"
                    :submitting="submitting"
                    :validation-errors="validationErrors"
                    @submit="handleTransferSubmit"
                    @clear="handleClearForm"
                    class="lg:col-span-2"
                />
            </div>

            <!-- Transaction History -->
            <TransactionHistory
                :transactions="transactions"
                :loading="loading"
                class="mt-8"
            />
        </div>
    </div>
</template>
