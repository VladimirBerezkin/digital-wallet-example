<script setup>
import Card from "primevue/card";

defineProps({
    balance: {
        type: String,
        required: true,
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
</script>

<template>
    <Card class="shadow-md">
        <template #title>
            <div class="flex items-center gap-3 px-6 pt-6">
                <i class="pi pi-wallet text-2xl text-primary"></i>
                <span class="text-xl font-bold text-gray-800"
                    >Current Balance</span
                >
            </div>
        </template>
        <template #content>
            <div class="px-6 pb-6 pt-4">
                <div
                    class="rounded-lg bg-gradient-to-br from-green-50 to-emerald-50 px-4 py-6 text-center"
                >
                    <p class="break-words text-3xl font-bold text-green-600">
                        {{ formatCurrency(balance) }}
                    </p>
                    <p class="mt-2 text-sm text-gray-600">
                        Available to transfer
                    </p>
                </div>
            </div>
        </template>
    </Card>
</template>
