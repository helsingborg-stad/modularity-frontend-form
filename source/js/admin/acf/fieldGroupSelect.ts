class FieldGroupSelect implements FieldGroupSelectInterface {
	private constructor(
		private store: StoreInterface,
		private field: HTMLElement,
	) {}

	private init(): this {
		return this;
	}

	public getFieldElement(): HTMLElement {
		return this.field;
	}

	public static createInstance(
		store: StoreInterface,
		field: HTMLElement,
	): FieldGroupSelect {
		return new FieldGroupSelect(store, field).init();
	}
}

export default FieldGroupSelect;
