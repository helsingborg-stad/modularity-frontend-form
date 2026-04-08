import LayoutOptionFragments from "../layoutOptionFragments";
import BasicLayoutUi from "./basicUI";

type ConditionalLogicState = {
    targetId: string;
    operator: string;
    value: string;
};

class BasicLayout implements BasicLayoutInterface {
    constructor(
        protected layoutData: LayoutData,
        protected layoutUI: BasicLayoutUi
    ) {
        this.setConditionalSelectListener();
        this.setConditionalOperatorSelectListener();
        this.setConditionalValueSelectListener();
    }

    public init(): void {
        this.restoreConditionalLogicState();
        this.updateConditionalState();
    }

    private setConditionalSelectListener(): void {
        this.layoutUI.onConditionalSelectChange(() => {
            this.saveConditionalLogicState();
            this.updateConditionalState();
            this.layoutData.layout.dispatchEvent(new CustomEvent('layout:conditional-changed', {
                detail: { layoutId: this.layoutData.layoutId }
            }));
        });
    }

    private setConditionalOperatorSelectListener(): void {
        this.layoutUI.onConditionalOperatorSelectChange(() => {
            this.updateConditionalValueSelectDisabledState();
            this.saveConditionalLogicState();
        });
    }

    private setConditionalValueSelectListener(): void {
        this.layoutUI.onConditionalLogicValueSelectChange(() => {
            this.saveConditionalLogicState();
        });
    }

    private updateConditionalState(): void {
        this.maybeDisableConditionalOperatorSelect();
        this.updateConditionalOperatorSelectOptions();
        this.updateConditionalValueSelectDisabledState();
    }

    private updateConditionalValueSelectDisabledState(): void {
        const isContainsOperator = this.layoutUI.getConditionalOperatorValue() === '==contains';
        this.layoutUI.setConditionalLogicValueSelectDisabled(!isContainsOperator);
    }

    private isSelectableValuesLayout(layout: BasicLayoutInterface | null): layout is SelectableValuesLayoutInterface {
        return !!layout && layout.getType() === 'radio' && 'getValues' in layout;
    }

    private maybeDisableConditionalOperatorSelect(): void {
        this.layoutUI.setConditionalOperatorSelectDisabled(!this.getSavedConditionalLogicValue());
    }

    private updateConditionalOperatorSelectOptions(): void {
        const conditionalValueLayout = this.layoutData.store.get(this.layoutUI.getConditionalSelectValue());
        const includeContains = this.isSelectableValuesLayout(conditionalValueLayout);
        const operatorOptions = LayoutOptionFragments.createConditionalOperatorOptionsFragment(includeContains);
        const selectedOperator = this.getSavedConditionalLogicState()?.operator ?? this.layoutUI.getConditionalOperatorValue();

        this.layoutUI.renderConditionalOperatorSelectOptions(operatorOptions, selectedOperator);
    }

    private saveConditionalLogicState(): void {
        const state: ConditionalLogicState = {
            targetId: this.layoutUI.getConditionalSelectValue(),
            operator: this.layoutUI.getConditionalOperatorValue(),
            value: this.layoutUI.getConditionalLogicValueSelectValue()
        };

        this.layoutUI.setSavedConditionalLogicValue(JSON.stringify(state));
    }

    private restoreConditionalLogicState(): void {
        const state = this.getSavedConditionalLogicState();

        if (!state) {
            return;
        }

        if (state.targetId) {
            this.layoutUI.setConditionalSelectValue(state.targetId);
        }

        if (state.operator) {
            this.layoutUI.setConditionalOperatorValue(state.operator);
        }

        if (state.value) {
            this.layoutUI.setConditionalLogicValueSelectValue(state.value);
        }
    }

    private getSavedConditionalLogicState(): ConditionalLogicState | null {
        const rawValue = this.layoutUI.getSavedConditionalLogicValue();

        if (!rawValue) {
            return null;
        }

        try {
            const parsedValue = JSON.parse(rawValue) as Partial<ConditionalLogicState>;

            if (typeof parsedValue !== 'object' || parsedValue === null) {
                return null;
            }

            return {
                targetId: parsedValue.targetId ?? '',
                operator: parsedValue.operator ?? '',
                value: parsedValue.value ?? ''
            };
        } catch {
            return null;
        }
    }

    public getSavedConditionalLogicValue(): string {
        return this.getSavedConditionalLogicState()?.targetId ?? '';
    }

    public getId(): string {
        return this.layoutData.layoutId;
    }

    public getNameField(): HTMLInputElement {
        return this.layoutUI.getNameField();
    }

    public getType(): string {
        return this.layoutData.layoutType;
    }

    public updateConditionalSelectOptions(optionsNodes: Node): void {
        this.layoutUI.renderConditionalSelectOptions(optionsNodes, this.layoutData.layoutId, this.getSavedConditionalLogicValue());
    }

    public updateConditionalSelectValuesOptions(optionsNodes: Node, values: OptionValues[]): void {
        const selectedValue = this.getSavedConditionalLogicState()?.value ?? this.layoutUI.getConditionalLogicValueSelectValue();

        this.layoutUI.renderConditionalSelectValuesOptions(
            optionsNodes,
            selectedValue,
            values
        );

        const selectedValueAfterRender = this.layoutUI.getConditionalLogicValueSelectValue();

        if (selectedValueAfterRender !== selectedValue) {
            this.saveConditionalLogicState();
        }
    }
}

export default BasicLayout;