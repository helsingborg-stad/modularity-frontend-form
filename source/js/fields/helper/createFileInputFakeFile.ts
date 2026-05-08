declare global {
	interface Window {
		addFakeFileToInput: (file: FakeFile, input: HTMLInputElement) => void;
	}
}

export default function createFileInputFakeFile(file: FakeFile, input: HTMLInputElement): void {
	window.addFakeFileToInput(
		{
			name: file.name,
			type: file.type || 'image/jpeg',
			id: file.id,
			url: file.url,
			size: file.size,
		},
		input,
	);
}
