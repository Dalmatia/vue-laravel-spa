import { ref, reactive } from 'vue';
import { useOutfitItemForm } from './useOutfitItemForm';
import { useOutfitImage } from './useOutfitImage';
import { OUTFIT_ROLE_META } from '../../constants/outfitRoles';

export function useOutfitForm(initialData = null) {
    const form = reactive({
        description: null,
        outfit_date: '',
        season: null,
    });

    const error = ref({});

    // コーディネートに使用したアイテムのカテゴリー
    const itemTypes = OUTFIT_ROLE_META;

    const { items, handleItemSelected, normalizeItems, getItemByRole } =
        useOutfitItemForm(initialData?.items ?? []);

    const { file, fileDisplay, isValidFile, getUploadedImage } =
        useOutfitImage();

    if (initialData) {
        Object.assign(form, initialData);
        normalizeItems(initialData.items);
    }

    const buildFormData = () => {
        const payload = new FormData();

        if (file.value instanceof File) {
            payload.append('file', file.value);
        }
        payload.append('description', form.description);
        payload.append('outfit_date', form.outfit_date);
        payload.append('season', form.season);
        payload.append('items', JSON.stringify(items.value));
        return payload;
    };

    const resetForm = () => {
        form.description = null;
        form.outfit_date = '';
        form.season = null;

        items.value = [];
        file.value = null;
        fileDisplay.value = '';
    };

    return {
        form,
        items,
        error,
        itemTypes,
        getItemByRole,
        handleItemSelected,
        fileDisplay,
        isValidFile,
        getUploadedImage,
        buildFormData,
        resetForm,
    };
}
