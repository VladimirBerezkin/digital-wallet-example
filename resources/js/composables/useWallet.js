import { ref } from "vue";
import axios from "axios";
import { isLocalEnv } from "../utils/env.js";

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
        // Only log in local environment
        if (isLocalEnv()) {
            console.log('Setting up Echo listener for user:', userId);
            console.log('Echo available:', !!window.Echo);
            console.log('Echo auth:', window.Echo?.options?.auth);
        }

        // Wait for both Echo and token to be available
        const waitForEchoAndToken = () => {
            const token = localStorage.getItem('token');

            if (window.Echo && token) {
                // Ensure Echo auth is properly set up before subscribing
                window.Echo.options.auth = {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                };

                const channelName = `user.${userId}`;
                const channel = window.Echo.private(channelName);

                if (isLocalEnv()) {
                    console.log('Echo auth ensured before subscription:', window.Echo.options.auth.headers);
                    console.log('Subscribing to private channel: user.' + userId);
                    console.log('Channel name:', channelName);
                    console.log('Pusher connection state:', window.Echo.connector.pusher.connection.state);
                    console.log('Pusher socket ID:', window.Echo.connector.pusher.connection.socket_id);
                }

                // Add subscription success callback first
                channel.subscribed(() => {
                    const pusherChannel = window.Echo.connector.pusher.channels.channels[`private-${channelName}`];

                    if (isLocalEnv()) {
                        console.log('Successfully subscribed to channel: user.' + userId);
                        console.log('Channel subscription confirmed for:', channelName);
                        console.log('Channel state:', channel.subscribed);
                        console.log('Pusher channel:', pusherChannel);
                    }

                    // Add debugging for Pusher events on this specific channel (only in development)
                    if (pusherChannel && import.meta.env.DEV) {
                        // Bind to specific events for debugging
                        pusherChannel.bind('pusher:subscription_succeeded', function(data) {
                            console.log('Pusher subscription succeeded on channel:', pusherChannel.name, data);
                        });

                        pusherChannel.bind('pusher:subscription_error', function(data) {
                            console.error('Pusher subscription error on channel:', pusherChannel.name, data);
                        });
                    }

                    // Listen for the TransferCompleted event after subscription is confirmed
                    // Use . prefix because the event has a custom broadcastAs() method
                    channel.listen(".TransferCompleted", (data) => {
                        if (isLocalEnv()) {
                            console.log('Received TransferCompleted event:', data);
                        }
                        updateFromEvent(data);
                    });
                });

                channel.error((error) => {
                    if (isLocalEnv()) {
                        console.error('Echo channel error:', error);
                    }
                });
            } else {
                if (isLocalEnv()) {
                    console.log('Waiting for Echo or token...', {
                        echo: !!window.Echo,
                        token: !!token
                    });
                }

                // Retry after 500ms
                setTimeout(waitForEchoAndToken, 500);
            }
        };

        waitForEchoAndToken();
    };

    const updateFromEvent = (eventData) => {
        // Refresh transactions to get the latest data
        fetchTransactions();
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
