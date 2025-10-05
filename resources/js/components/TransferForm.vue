<script setup>
import { ref, watch } from "vue";
import Card from "primevue/card";
import InputNumber from "primevue/inputnumber";
import Textarea from "primevue/textarea";
import Button from "primevue/button";
import Message from "primevue/message";

const props = defineProps({
    receiverId: {
        type: Number,
        default: null,
    },
    submitting: {
        type: Boolean,
        default: false,
    },
    validationErrors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(["submit", "update:receiverId", "clear"]);

const localReceiverId = ref(props.receiverId);
const amount = ref(null);
const description = ref("");

// Watch for external changes to receiverId
watch(
    () => props.receiverId,
    (newValue) => {
        localReceiverId.value = newValue;
    },
);

const handleSubmit = () => {
    emit("submit", {
        receiverId: localReceiverId.value,
        amount: amount.value,
        description: description.value,
    });
};

const handleClear = () => {
    localReceiverId.value = null;
    amount.value = null;
    description.value = "";
    emit("clear");
};

// Expose clear method for parent component
defineExpose({
    clear: () => {
        amount.value = null;
        description.value = "";
    },
});

const hasError = (field) => {
    return props.validationErrors && props.validationErrors[field];
};

const getErrorMessage = (field) => {
    const errors = props.validationErrors?.[field];
    return errors ? (Array.isArray(errors) ? errors[0] : errors) : "";
};
</script>

<template>
    <Card class="shadow-md">
        <template #title>
            <div class="flex items-center gap-3 px-6 pt-6">
                <i class="pi pi-send text-2xl text-primary"></i>
                <span class="text-xl font-bold text-gray-800">Send Money</span>
            </div>
        </template>
        <template #content>
            <div class="px-6 pb-6 pt-4">
                <form
                    @submit.prevent="handleSubmit"
                    class="flex flex-col gap-5"
                >
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label
                                for="receiver"
                                class="mb-2 block text-sm font-semibold text-gray-700"
                                :class="{
                                    'text-red-700': hasError('receiver_id'),
                                }"
                            >
                                Receiver ID
                            </label>
                            <InputNumber
                                id="receiver"
                                v-model="localReceiverId"
                                placeholder="Enter user ID"
                                :useGrouping="false"
                                :disabled="submitting"
                                class="w-full"
                                :class="{
                                    'p-invalid': hasError('receiver_id'),
                                }"
                            />
                            <small
                                v-if="!hasError('receiver_id')"
                                class="mt-1 text-xs text-gray-500"
                                >Enter the ID of the user you want to send money
                                to</small
                            >
                            <small
                                v-else
                                class="mt-1 block text-xs text-red-600"
                            >
                                <i class="pi pi-exclamation-circle mr-1"></i>
                                {{ getErrorMessage("receiver_id") }}
                            </small>
                        </div>

                        <div>
                            <label
                                for="amount"
                                class="mb-2 block text-sm font-semibold text-gray-700"
                                :class="{ 'text-red-700': hasError('amount') }"
                            >
                                Amount
                            </label>
                            <InputNumber
                                id="amount"
                                v-model="amount"
                                mode="currency"
                                currency="USD"
                                locale="en-US"
                                placeholder="0.00"
                                :minFractionDigits="2"
                                :maxFractionDigits="2"
                                :disabled="submitting"
                                class="w-full"
                                :class="{ 'p-invalid': hasError('amount') }"
                            />
                            <small
                                v-if="!hasError('amount')"
                                class="mt-1 text-xs text-gray-500"
                                >Minimum transfer: $0.01</small
                            >
                            <small
                                v-else
                                class="mt-1 block text-xs text-red-600"
                            >
                                <i class="pi pi-exclamation-circle mr-1"></i>
                                {{ getErrorMessage("amount") }}
                            </small>
                        </div>
                    </div>

                    <div>
                        <label
                            for="description"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                            :class="{ 'text-red-700': hasError('description') }"
                        >
                            Description
                            <span class="font-normal text-gray-500"
                                >(Optional)</span
                            >
                        </label>
                        <Textarea
                            id="description"
                            v-model="description"
                            rows="3"
                            placeholder="Enter transfer description (e.g., 'Lunch payment', 'Rent')"
                            :disabled="submitting"
                            class="w-full"
                            :class="{ 'p-invalid': hasError('description') }"
                        />
                        <small
                            v-if="hasError('description')"
                            class="mt-1 block text-xs text-red-600"
                        >
                            <i class="pi pi-exclamation-circle mr-1"></i>
                            {{ getErrorMessage("description") }}
                        </small>
                    </div>

                    <div
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4"
                    >
                        <div class="flex items-start gap-3">
                            <i
                                class="pi pi-info-circle mt-0.5 text-blue-600"
                            ></i>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-blue-900">
                                    Commission Fee
                                </p>
                                <p class="mt-1 text-xs text-blue-700">
                                    A 1.5% commission fee will be automatically
                                    deducted from your balance
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button
                            type="submit"
                            label="Send Money"
                            icon="pi pi-send"
                            :loading="submitting"
                            class="flex-1"
                            size="large"
                        />
                        <Button
                            type="button"
                            label="Clear"
                            icon="pi pi-times"
                            severity="secondary"
                            outlined
                            :disabled="submitting"
                            @click="handleClear"
                        />
                    </div>
                </form>
            </div>
        </template>
    </Card>
</template>
