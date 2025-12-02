class Store implements StoreInterface {
	private static instance: Store;
	private static fieldStorage: { [key: string]: FieldStorage } = {};
	private constructor() {}

	/**
	 * Sets the field storage for a given ID.
	 *
	 * @param id
	 * @param fieldStorage
	 */
	public set(id: string, fieldStorage: FieldStorage): void {
		Store.fieldStorage[id] = fieldStorage;
	}

	/**
	 * Retrieves the field storage for a given ID.
	 *
	 * @param id
	 */
	public get(id: string): FieldStorage | null {
		return Store.fieldStorage[id] ? Store.fieldStorage[id] : null;
	}

	/**
	 * Adds a field to a group for a given ID.
	 *
	 * @param id
	 * @param field
	 */
	public addFieldToGroup(id: string, field: FieldGroupSelectInterface): void {
		if (Store.fieldStorage[id]) {
			Store.fieldStorage[id].fields.push(field);
		}
	}

	/**
	 * Initializes and returns the singleton instance of the Store.
	 */
	public static initStore(): Store {
		if (!Store.instance) {
			Store.instance = new Store();
		}

		return Store.instance;
	}
}

export default Store;
