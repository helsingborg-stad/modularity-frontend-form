import Basic from "../basic/basic";

class File extends Basic implements FieldInterface {
    protected onInput(): void {
        this.getValidator().validate();
    }
}

export default File;