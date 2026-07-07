import { onMounted, computed } from 'vue';
import { useEnumStore } from '../../stores/enum';

export function useInitEnums() {
    const enumStore = useEnumStore();

    onMounted(() => {
        enumStore.fetchEnums();
    });

    return enumStore;
}
