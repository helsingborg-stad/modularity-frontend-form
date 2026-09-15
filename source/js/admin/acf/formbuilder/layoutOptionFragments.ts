export function createConditionalValueOptionsFragment(values: OptionValues[]): DocumentFragment {
    const fragment = document.createDocumentFragment();

    values.forEach(({ key, label }) => {
        const option = document.createElement('option');
        option.value = key;
        option.textContent = label;
        fragment.appendChild(option);
    });

    return fragment;
}

export function createConditionalOperatorOptionsFragment(includeContains: boolean): DocumentFragment {
    const fragment = document.createDocumentFragment();
    const operatorOptions = [
        { value: '!=empty', label: 'Has value' },
        { value: '==empty', label: 'Has no value' },
        ...(includeContains ? [{ value: '==contains', label: 'Contains' }] : [])
    ];

    operatorOptions.forEach(({ value, label }) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        fragment.appendChild(option);
    });

    return fragment;
}

export function createLayoutOptionsFragment(layouts: (BasicLayoutInterface | SelectableValuesLayoutInterface)[]): DocumentFragment {
    const fragment = document.createDocumentFragment();

    const none = document.createElement('option');
    none.value = '';
    none.textContent = 'None';
    fragment.appendChild(none);

    layouts.forEach(layout => {
        const option = document.createElement('option');
        option.value = layout.getId();
        option.textContent = layout.getNameField().value || 'Unnamed Layout';
        fragment.appendChild(option);
    });

    return fragment;
}
