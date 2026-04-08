class LayoutOptionFragments {
    public static createConditionalValueOptionsFragment(values: OptionValues[]): DocumentFragment {
        const fragment = document.createDocumentFragment();

        values.forEach(optionValue => {
            const option = document.createElement('option');
            option.value = optionValue.key;
            option.textContent = optionValue.label;
            fragment.appendChild(option);
        });

        return fragment;
    }

    public static createConditionalOperatorOptionsFragment(includeContains: boolean): DocumentFragment {
        const fragment = document.createDocumentFragment();
        const operatorOptions = [
            { value: '!=empty', label: 'Has value' },
            { value: '==empty', label: 'Has no value' }
        ];

        if (includeContains) {
            operatorOptions.push({ value: '==contains', label: 'Contains' });
        }

        operatorOptions.forEach(operatorOption => {
            const option = document.createElement('option');
            option.value = operatorOption.value;
            option.textContent = operatorOption.label;
            fragment.appendChild(option);
        });

        return fragment;
    }

    public static createLayoutOptionsFragment(layouts: (BasicLayoutInterface | SelectableValuesLayoutInterface)[]): DocumentFragment {
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
}

export default LayoutOptionFragments;
