import LayoutOptionFragments from "../layoutOptionFragments";
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
        this.updateConditionalOperatorSelectOptions();
        this.maybeDisableConditionalOperatorSelect();
    }

    private maybeDisableConditionalLogicValueSelect(): void {
        const conditionalValueLayout = this.layoutData.store.get(this.layoutUI.getConditionalSelectValue());
        const isSelectableLayout = this.isSelectableValuesLayout(conditionalValueLayout);

        this.layoutUI.setConditionalLogicValueSelectDisabled(!isSelectableLayout);
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

        this.layoutUI.renderConditionalOperatorSelectOptions(operatorOptions, this.layoutUI.getConditionalOperatorValue());
    }

    private saveConditionalLogicValue(): void {
        this.layoutUI.setSavedConditionalLogicValue(this.layoutUI.getConditionalSelectValue());
    }

    public getSavedConditionalLogicValue(): string {
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

    public updateConditionalSelectValuesOptions(optionsNodes: Node): void {
        this.layoutUI.renderConditionalSelectValuesOptions(optionsNodes, this.getSavedConditionalLogicValue());
    }
}

export default BasicLayout;