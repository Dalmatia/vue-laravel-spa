export function useDebounce(fn, delay = 100) {
    let timeout;

    const debounced = (...args) => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            fn(...args);
        }, delay);
    };

    debounced.cancel = () => {
        clearTimeout(timeout);
    };

    return debounced;
}
