import { reactive, ref, watch } from 'vue';

export function useEditItemForm(editItem, emitClose) {
    const editForm = ref({ ...editItem });
    const isValidFile = ref(null);
    const fileDisplay = ref('');

    const formErrors = reactive({
        file: null,
        main_category: null,
        sub_category: null,
        color: null,
        season: null,
        memo: null,
    });

    const clearFormErrors = () => {
        Object.keys(formErrors).forEach((key) => {
            formErrors[key] = null;
        });
    };

    const handleApiError = (errors) => {
        const responseErrors = errors.response?.data?.errors || {};
        Object.entries(formErrors).forEach(([key]) => {
            formErrors[key] = responseErrors[key] || null;
        });
    };

    const updateItem = async () => {
        clearFormErrors();

        const formData = new FormData();
        if (editForm.value.file instanceof File) {
            formData.append('file', editForm.value.file);
        }
        Object.entries(editForm.value).forEach(([key, value]) => {
            if (key === 'file') return;
            if (key === 'memo') {
                // 'null' 文字列を空に変換して送信（nullable対応）
                formData.append('memo', value === 'null' ? '' : value ?? '');
            } else {
                const normalizedValue =
                    value === '' || value === null || value === 'null'
                        ? ''
                        : value;
                formData.append(key, normalizedValue);
            }
        });
        try {
            const response = await axios.post(
                `/api/items/${editItem.id}`,
                formData,
                {
                    forceFormData: true,
                    preserveScroll: true,
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                }
            );

            if (response.status === 200) {
                emitClose();
                window.dispatchEvent(new Event('item-updated'));
            }
        } catch (error) {
            console.error('アイテム情報の更新に失敗しました。', error);
            handleApiError(error);
        }
    };

    const getUploadedImage = (e) => {
        editForm.value.file = e.target.files[0];
        const validTypes = ['image/png', 'image/jpeg', 'image/webp'];

        if (!validTypes.includes(editForm.value.file.type.toLowerCase())) {
            isValidFile.value = false;
            editForm.value.file = null;
            fileDisplay.value = '';
            return;
        }

        isValidFile.value = true;
        fileDisplay.value = URL.createObjectURL(editForm.value.file);
        const img = new Image();
        img.onload = () => {
            setTimeout(() => {
                document
                    .getElementById('TextAreaSection')
                    ?.scrollIntoView({ behavior: 'smooth' });
            }, 300);
        };
    };

    watch(
        () => editForm.value.sub_category,
        (val) => {
            if (val === '') editForm.value.sub_category = null;
        }
    );
    watch(
        () => editForm.value.season,
        (val) => {
            if (val === '') editForm.value.season = null;
        }
    );

    // propsの変更に対応（念のため）
    watch(
        () => editItem,
        (newVal) => {
            editForm.value = { ...newVal };
        },
        { deep: true }
    );

    return {
        editForm,
        formErrors,
        isValidFile,
        fileDisplay,
        updateItem,
        getUploadedImage,
        clearFormErrors,
    };
}
