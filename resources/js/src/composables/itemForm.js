import { ref, reactive } from 'vue';

export function useItemForm(emitClose) {
    const form = reactive({
        file: null,
        main_category: '',
        sub_category: null,
        color: null,
        season: null,
        memo: null,
    });

    const formErrors = ref({
        file: null,
        main_category: '',
        sub_category: '',
        color: '',
        season: '',
        memo: null,
    });

    const isValidFile = ref(null);
    const fileDisplay = ref('');

    const clearFormErrors = () => {
        Object.keys(formErrors.value).forEach((key) => {
            formErrors.value[key] = null;
        });
    };

    const normalizeFormValues = () => {
        // サブカテゴリーとシーズンが空文字ならnullに変換
        form.sub_category = form.sub_category === '' ? null : form.sub_category;
        form.season = form.season === '' ? null : form.season;
    };

    const handleApiError = (errors) => {
        const responseErrors = errors.response?.data?.errors || {};
        Object.entries(formErrors.value).forEach(([key]) => {
            formErrors.value[key] = responseErrors[key] || null;
        });
    };

    // クローゼットアイテム登録
    const registerItem = async () => {
        clearFormErrors();
        normalizeFormValues();
        try {
            const response = await axios.post('/api/items', form, {
                forceFormData: true,
                preserveScroll: true,
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            if (response.status === 200) {
                emitClose();
                window.dispatchEvent(new Event('item-registered'));
            }
        } catch (error) {
            console.error('アイテムの登録に失敗しました。', error);
            handleApiError(error);
        }
    };

    // ファイルアップロード
    const getUploadedImage = (e) => {
        form.file = e.target.files[0];
        const extension = form.file.name.split('.').pop().toLowerCase();

        if (!['jpg', 'jpeg', 'png'].includes(extension)) {
            isValidFile.value = false;
            return;
        }

        isValidFile.value = true;
        fileDisplay.value = URL.createObjectURL(form.file);
        setTimeout(() => {
            document
                .getElementById('TextAreaSection')
                .scrollIntoView({ behavior: 'smooth' });
        }, 300);
    };

    const resetForm = () => {
        form.file = null;
        form.main_category = '';
        form.sub_category = null;
        form.color = null;
        form.season = null;
        form.memo = null;
        isValidFile.value = null;
        fileDisplay.value = '';
        clearFormErrors();
    };

    return {
        form,
        formErrors,
        isValidFile,
        fileDisplay,
        registerItem,
        getUploadedImage,
        resetForm,
    };
}
