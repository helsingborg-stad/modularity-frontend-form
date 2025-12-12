class FileValueLoader implements BasicValueLoaderInterface {
	private parent!: BasicInterface;

	public init(parent: BasicInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		console.log(value);
	}
}

export default FileValueLoader;
