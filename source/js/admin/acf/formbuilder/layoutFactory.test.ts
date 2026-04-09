import LayoutFactory from "./layoutFactory";
import { layoutDefinitions } from "./layoutConfig";
import BasicLayout from "./layouts/templates/basic";

describe('LayoutFactory', () => {
    const store: FormBuilderStoreInterface = {
        add: jest.fn(),
        remove: jest.fn(),
        get: jest.fn(() => null),
        getAll: jest.fn(() => [])
    };

    beforeEach(() => {
        (globalThis as typeof globalThis & { acf: { addAction: jest.Mock } }).acf = {
            addAction: jest.fn()
        };
    });

    describe.each(layoutDefinitions.map(({ type, LayoutClass }) => [type, LayoutClass]))('createLayout for %s', (layoutType, ExpectedLayoutClass) => {
        it(`returns ${ExpectedLayoutClass.name}`, () => {
            const factory = new LayoutFactory(store);
            const layout = createLayoutElement(layoutType);

            const result = factory.createLayout(layout);

            expect(result).toBeInstanceOf(ExpectedLayoutClass);
        });
    });

    it('falls back to BasicLayout for unknown layout types', () => {
        const factory = new LayoutFactory(store);
        const layout = createLayoutElement('unknown');

        const result = factory.createLayout(layout);

        expect(result).toBeInstanceOf(BasicLayout);
    });

    it('returns null when required fields are missing', () => {
        const factory = new LayoutFactory(store);
        const layout = document.createElement('div');
        layout.setAttribute('data-layout', 'formbuilder-text');

        const result = factory.createLayout(layout);

        expect(result).toBeNull();
    });
});

function createLayoutElement(layoutType: string): HTMLElement {
    const layout = document.createElement('div');
    layout.setAttribute('data-layout', `formbuilder-${layoutType}`);
    layout.innerHTML = `
        <div data-name="hidden_id"><input value="layout-${layoutType}" /></div>
        <div data-name="name"><input value="${layoutType}" /></div>
        <div data-name="conditional_logic"><select></select></div>
        <div data-name="conditional_logic_operator"><select></select></div>
        <div data-name="conditional_logic_value"><select multiple></select></div>
        <div data-name="hidden_conditional_logic"><input value="" /></div>
        <div data-name="options"></div>
    `;

    return layout;
}