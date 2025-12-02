interface AcfSelectsInterface {
	init(): void;
}

interface AcfSelect {
	updateOptions(options: any[]): void;
}

interface StoreInterface {
	set(id: string, fieldStorage: FieldStorage): void;
	get(id: string): FieldStorage | null;
	addFieldToGroup(id: string, field: FieldGroupSelectInterface): void;
}

interface FieldGroupSelectInterface {
	getFieldElement(): HTMLElement;
}

interface PostTypeSelectInterface {
	getSelected(): string | null;
}
