import CheckboxLayout from "./layouts/checkboxLayout";
import RadioLayout from "./layouts/radioLayout";
import SelectLayout from "./layouts/selectLayout";
import BasicLayout from "./layouts/templates/basic";
import BasicLayoutUI from "./layouts/templates/basicUi";
import ConditionalLogicController from "./layouts/templates/conditionalLogicController";
import SelectableValuesLayoutUI from "./layouts/templates/selectableValuesLayoutUi";

export type BasicLayoutConstructor = new (layoutData: LayoutData, layoutUI: BasicLayoutUI, conditionalLogicController: ConditionalLogicController) => BasicLayoutInterface;
export type SelectableLayoutConstructor = new (layoutData: LayoutData, layoutUI: SelectableValuesLayoutUI, conditionalLogicController: ConditionalLogicController) => SelectableValuesLayoutInterface;

export type BasicLayoutDefinition = {
    type: string;
    kind: 'basic';
    LayoutClass: BasicLayoutConstructor;
};

export type SelectableLayoutDefinition = {
    type: string;
    kind: 'selectable';
    LayoutClass: SelectableLayoutConstructor;
};

export type LayoutDefinition = BasicLayoutDefinition | SelectableLayoutDefinition;

const basicLayoutTypes = [
    'date',
    'email',
    'file',
    'gallery',
    'googleMap',
    'image',
    'message',
    'number',
    'text',
    'textarea',
    'time',
    'trueFalse',
    'url',
    'wysiwyg'
] as const;

export const layoutDefinitions: LayoutDefinition[] = [
    { type: 'checkbox', kind: 'selectable', LayoutClass: CheckboxLayout },
    { type: 'radio', kind: 'selectable', LayoutClass: RadioLayout },
    { type: 'select', kind: 'selectable', LayoutClass: SelectLayout },
    ...basicLayoutTypes.map(type => ({ type, kind: 'basic' as const, LayoutClass: BasicLayout }))
];
