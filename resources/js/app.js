const filterForm = document.querySelector('#announcement-filters');

if (filterForm) {
    let searchTimer;

    filterForm.querySelector('input[name="q"]').addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => filterForm.requestSubmit(), 350);
    });

    filterForm.querySelectorAll('select').forEach((select) => {
        select.addEventListener('change', () => filterForm.requestSubmit());
    });
}

const imageInput = document.querySelector('#image');
const imagePreview = document.querySelector('#image-preview');

if (imageInput && imagePreview) {
    imageInput.addEventListener('change', () => {
        imagePreview.replaceChildren();

        const [file] = imageInput.files;

        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const preview = document.createElement('img');
        preview.alt = 'Prévia da nova imagem';
        preview.src = URL.createObjectURL(file);
        imagePreview.append(preview);
        preview.addEventListener('load', () => URL.revokeObjectURL(preview.src), { once: true });
    });
}
