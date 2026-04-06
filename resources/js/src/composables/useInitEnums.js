import { onMounted, computed } from 'vue';
import { useEnumStore } from '../../stores/enum';

export function useInitEnums() {
    const enumStore = useEnumStore();

    onMounted(() => {
        enumStore.fetchEnums();
    });

    return {
        genders: computed(() => enumStore.genders),
        mainCategories: computed(() => enumStore.mainCategories),
        subCategories: computed(() => enumStore.subCategories),
        seasons: computed(() => enumStore.seasons),
        colors: computed(() => enumStore.colors),
    };
}
