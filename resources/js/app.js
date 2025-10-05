import "./bootstrap";
import { createApp } from "vue";
import PrimeVue from "primevue/config";
import Aura from "@primevue/themes/aura";
import ToastService from "primevue/toastservice";
import App from "./components/App.vue";

const app = createApp(App);

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: ".dark-mode",
            cssLayer: {
                name: "primevue",
                order: "tailwind-base, primevue, tailwind-utilities",
            },
        },
    },
});

app.use(ToastService);

app.mount("#app");
