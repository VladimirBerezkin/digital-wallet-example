<script setup>
import Card from "primevue/card";
import Tag from "primevue/tag";

const props = defineProps({
    currentUserId: {
        type: Number,
        required: true,
    },
    selectedReceiverId: {
        type: Number,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["select-user"]);

const testUsers = [
    { id: 1, name: "Alice Johnson", initialBalance: "$1,000.00" },
    { id: 2, name: "Bob Smith", initialBalance: "$500.00" },
    { id: 3, name: "Charlie Brown", initialBalance: "$0.00" },
    { id: 4, name: "Diana Prince", initialBalance: "$10,000.00" },
];

const selectUser = (userId) => {
    emit("select-user", userId);
};
</script>

<template>
    <Card class="shadow-md">
        <template #title>
            <div class="flex items-center gap-3 px-6 pt-6">
                <i class="pi pi-users text-2xl text-primary"></i>
                <span class="text-xl font-bold text-gray-800">Test Users</span>
            </div>
        </template>
        <template #content>
            <div class="px-6 pb-6 pt-4">
                <p class="mb-4 text-sm text-gray-600">
                    Click to auto-fill Receiver ID
                </p>
                <div class="flex flex-col gap-2">
                    <button
                        v-for="testUser in testUsers"
                        :key="testUser.id"
                        @click="selectUser(testUser.id)"
                        :disabled="disabled || testUser.id === currentUserId"
                        class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3 text-left transition-all hover:border-blue-300 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:border-gray-200 disabled:hover:bg-white"
                        :class="{
                            'border-blue-500 bg-blue-50':
                                selectedReceiverId === testUser.id,
                        }"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-blue-200"
                            >
                                <span class="text-sm font-bold text-blue-700"
                                    >#{{ testUser.id }}</span
                                >
                            </div>
                            <div>
                                <div
                                    class="text-sm font-semibold text-gray-800"
                                >
                                    {{ testUser.name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Initial: {{ testUser.initialBalance }}
                                </div>
                            </div>
                        </div>
                        <div v-if="testUser.id === currentUserId">
                            <Tag value="You" severity="info" class="text-xs" />
                        </div>
                        <div
                            v-else-if="selectedReceiverId === testUser.id"
                            class="text-blue-600"
                        >
                            <i class="pi pi-check-circle"></i>
                        </div>
                    </button>
                </div>
            </div>
        </template>
    </Card>
</template>
