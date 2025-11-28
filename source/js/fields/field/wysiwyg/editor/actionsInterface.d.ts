interface ActionsInterface {
	getDefaultParagraphSeparatorString(): string;
	getFormatBlock(): string;
	createIcon(name: string): string;
	queryCommandState(command: string): boolean;
	queryCommandValue(command: string): string;
	exec(command: string, value?: string): void;
	getDefaultActions(): EditorActions;
	getActiveActions(): string[];
}
