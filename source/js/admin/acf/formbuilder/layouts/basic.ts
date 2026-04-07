import BasicLayoutUi from "./basicUI";

class BasicLayout implements BasicLayoutInterface {
    constructor(
        protected layoutData: LayoutData,
        protected layoutUI: BasicLayoutUi
    ) {
        this.setConditionalSelectListener();
    }

    public init(): void {
        this.updateConditionalState();
    }

    private setConditionalSelectListener(): void {
        this.layoutUI.onConditionalSelectChange(() => {
            this.saveConditionalLogicValue();
            this.updateConditionalState();
        });
    }

    private updateConditionalState(): void {
        this.maybeDisableConditionalLogicValueSelect();
        this.maybeDisableConditionalOperatorSelect();
    }

    private maybeDisableConditionalLogicValueSelect(): void {
        const conditionalValueLayout = this.layoutData.store.get(this.layoutUI.getConditionalSelectValue());
        const isSelectableLayout = conditionalValueLayout?.getType() === 'radio';

        if (isSelectableLayout && this.isSelectableValuesLayout(conditionalValueLayout)) {
            const options = conditionalValueLayout.getValues();

        }

        this.layoutUI.setConditionalLogicValueSelectDisabled(!isSelectableLayout);
    }

    private isSelectableValuesLayout(layout: BasicLayoutInterface | null): layout is SelectableValuesLayoutInterface {
        return !!layout && layout.getType() === 'radio' && 'getValue' in layout;
    }

    private maybeDisableConditionalOperatorSelect(): void {
        this.layoutUI.setConditionalOperatorSelectDisabled(!this.getSavedConditionalLogicValue());
    }

    private saveConditionalLogicValue(): void {
        this.layoutUI.setSavedConditionalLogicValue(this.layoutUI.getConditionalSelectValue());
    }

    private getSavedConditionalLogicValue(): string {
        return this.layoutUI.getSavedConditionalLogicValue();
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
}

export default BasicLayout;