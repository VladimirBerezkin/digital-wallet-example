import { ref } from "vue";
import axios from "axios";

export function useWallet() {
    const balance = ref("0.00");
    const transactions = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const fetchTransactions = async () => {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await axios.get("/api/transactions");
            balance.value = data.balance;
            transactions.value = data.transactions;
            return { success: true };
        } catch (err) {
            error.value =
                err.response?.data?.message || "Failed to load transactions";
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    };

    const transfer = async (receiverId, amount, description) => {
        try {
            await axios.post("/api/transactions", {
                receiver_id: receiverId,
                amount,
                description,
            });
            await fetchTransactions();
            return { success: true };
        } catch (err) {
            return {
                success: false,
                error: err.response?.data?.message || "Transfer failed",
                errors: err.response?.data?.errors,
            };
        }
    };

    const listenForUpdates = (userId) => {
        if (window.Echo) {
            window.Echo.channel(`user.${userId}`).listen(
                "TransferCompleted",
                (data) => {
                    fetchTransactions();
                },
            );
        }
    };

    const stopListening = (userId) => {
        if (window.Echo) {
            window.Echo.leave(`user.${userId}`);
        }
    };

    return {
        balance,
        transactions,
        loading,
        error,
        transfer,
        fetchTransactions,
        listenForUpdates,
        stopListening,
    };
}
