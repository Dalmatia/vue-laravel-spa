export function useScrollContainer() {
    const getContainer = () => {
        return document.getElementById('scroll-container');
    };

    const scrollTo = (y = 0) => {
        const container = getContainer();

        if (!container) return;

        container.scrollTo(0, y);
    };

    const getScrollTop = () => {
        return getContainer()?.scrollTop ?? 0;
    };

    const addScrollListener = (callback) => {
        getContainer()?.addEventListener('scroll', callback);
    };

    const removeScrollListener = (callback) => {
        getContainer()?.removeEventListener('scroll', callback);
    };

    return {
        scrollTo,
        getScrollTop,
        addScrollListener,
        removeScrollListener,
    };
}
