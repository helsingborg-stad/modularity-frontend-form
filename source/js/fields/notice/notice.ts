class Notice implements NoticeInterface {
    private errorNotice: string;
    private successNotice: string;
    private errorNoticeTarget: string = '[data-js-frontend-form-error-notice]';
    private successNoticeTarget: string = '[data-js-frontend-form-success-notice]';
    constructor(private formContainer: HTMLElement) {
        this.errorNotice = this.extractNotice(this.errorNoticeTarget);
        this.successNotice = this.extractNotice(this.successNoticeTarget);
    }

    getErrorNotice(): HTMLElement {
        const div = document.createElement('div');
        div.innerHTML = this.errorNotice;

        return div;
    }

    getSuccessNotice(): HTMLElement {
        const div = document.createElement('div');
        div.innerHTML = this.successNotice;

        return div;
    }

    private extractNotice(target: string): string {
        const template = this.formContainer.querySelector(target) as HTMLTemplateElement;

        if (template) {
            return template.innerHTML;
        }

        console.error("Notice template not found");
        return "";
    }
}

export default Notice;