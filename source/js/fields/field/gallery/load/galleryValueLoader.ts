declare global {
	interface Window {
		addFakeFileToInput: (file: FakeFile, input: HTMLInputElement) => void;
	}
}

class GalleryValueLoader implements BasicValueLoaderInterface {
	private parent!: BasicInterface;

	public init(parent: BasicInterface): void {
		this.parent = parent;
	}

	public load(value: FakeFile[]): void {
		if (!value || Array.isArray(value) === false || value.length === 0) {
			return;
		}

		value.forEach((item: FakeFile) => {
			if (!item.id || !item.url) {
				return;
			}

			window.addFakeFileToInput(
				{
					name: item.name,
					type: item.type || 'image/jpeg',
					id: item.id,
					url: item.url,
					size: item.size,
				},
				this.parent.getField(),
			);
		});
	}
}

export default GalleryValueLoader;
