import { onMounted, computed } from 'vue';
import { useEnumStore } from '../../stores/enum';

export function useInitEnums() {
    const enumStore = useEnumStore();

    onMounted(async () => {
        await enumStore.fetchEnums();
    });

    return {
        genders: computed(() => enumStore.genders),
        mainCategories: computed(() => enumStore.mainCategories),
        subCategories: computed(() => enumStore.subCategories),
        seasons: computed(() => enumStore.seasons),
        scenes: computed(() => enumStore.scenes),
        colors: computed(() => enumStore.colors),
    };
}
