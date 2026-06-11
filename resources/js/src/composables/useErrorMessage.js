import { ref } from 'vue';

const errorMessage = ref('');

export function useErrorMessage() {
    const showError = (message) => {
        errorMessage.value = message;

        setTimeout(() => {
            errorMessage.value = '';
        }, 3000);
    };

    return {
        errorMessage,
        showError,
    };
}
