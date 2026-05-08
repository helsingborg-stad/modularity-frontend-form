import createFileInputFakeFile from '../../../helper/createFileInputFakeFile';

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

			createFileInputFakeFile(item, this.parent.getField());
		});
	}
}

export default GalleryValueLoader;
