<script setup>
import { ref, computed } from "vue";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Paginator from "primevue/paginator";

const props = defineProps({
    transactions: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const first = ref(0);
const rows = ref(10);

const paginatedTransactions = computed(() => {
    return props.transactions.slice(first.value, first.value + rows.value);
});

const onPageChange = (event) => {
    first.value = event.first;
    rows.value = event.rows;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
};

const getTransactionSeverity = (type) => {
    return type === "sent" ? "danger" : "success";
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <Card class="shadow-md">
        <template #title>
            <div class="flex items-center gap-3 px-6 pt-6">
                <i class="pi pi-history text-2xl text-primary"></i>
                <span class="text-xl font-bold text-gray-800"
                    >Transaction History</span
                >
            </div>
        </template>
        <template #content>
            <div class="px-6 pb-6 pt-4">
                <DataTable
                    :value="paginatedTransactions"
                    :loading="loading"
                    stripedRows
                    class="text-sm"
                >
                    <template #empty>
                        <div class="py-12 text-center">
                            <i
                                class="pi pi-inbox mb-4 text-6xl text-gray-300"
                            ></i>
                            <p class="text-lg font-semibold text-gray-600">
                                No transactions yet
                            </p>
                            <p class="mt-2 text-sm text-gray-500">
                                Start by sending money to another user
                            </p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable style="width: 80px">
                        <template #body="{ data }">
                            <span class="font-mono text-xs text-gray-500"
                                >#{{ data.id }}</span
                            >
                        </template>
                    </Column>

                    <Column
                        field="type"
                        header="Type"
                        sortable
                        style="width: 130px"
                    >
                        <template #body="{ data }">
                            <Tag
                                :value="
                                    data.type === 'sent' ? 'Sent' : 'Received'
                                "
                                :severity="getTransactionSeverity(data.type)"
                                :icon="
                                    data.type === 'sent'
                                        ? 'pi pi-arrow-up'
                                        : 'pi pi-arrow-down'
                                "
                            />
                        </template>
                    </Column>

                    <Column field="counterparty" header="Counterparty" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-user text-gray-400"></i>
                                <span class="font-medium text-gray-700">{{
                                    data.counterparty
                                }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="amount" header="Amount" sortable>
                        <template #body="{ data }">
                            <span
                                :class="
                                    data.type === 'sent'
                                        ? 'text-red-600'
                                        : 'text-green-600'
                                "
                                class="text-base font-bold"
                            >
                                {{ data.type === "sent" ? "-" : "+" }}
                                {{ formatCurrency(data.amount) }}
                            </span>
                        </template>
                    </Column>

                    <Column
                        field="commission"
                        header="Fee"
                        sortable
                        style="width: 100px"
                    >
                        <template #body="{ data }">
                            <span
                                v-if="data.commission"
                                class="font-medium text-gray-600"
                            >
                                {{ formatCurrency(data.commission) }}
                            </span>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </Column>

                    <Column field="description" header="Description">
                        <template #body="{ data }">
                            <span
                                v-if="data.description"
                                class="text-gray-700"
                                >{{ data.description }}</span
                            >
                            <span v-else class="italic text-gray-400"
                                >No description</span
                            >
                        </template>
                    </Column>

                    <Column
                        field="status"
                        header="Status"
                        sortable
                        style="width: 130px"
                    >
                        <template #body="{ data }">
                            <Tag
                                :value="data.status"
                                :severity="
                                    data.status === 'completed'
                                        ? 'success'
                                        : 'warning'
                                "
                                :icon="
                                    data.status === 'completed'
                                        ? 'pi pi-check-circle'
                                        : 'pi pi-clock'
                                "
                            />
                        </template>
                    </Column>

                    <Column field="date" header="Date" sortable>
                        <template #body="{ data }">
                            <div class="text-xs text-gray-600">
                                {{ formatDate(data.date) }}
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <Paginator
                    v-if="transactions.length > 0"
                    :first="first"
                    :rows="rows"
                    :totalRecords="transactions.length"
                    :rowsPerPageOptions="[5, 10, 20, 50]"
                    @page="onPageChange"
                    template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} transactions"
                    class="mt-4"
                />
            </div>
        </template>
    </Card>
</template>
