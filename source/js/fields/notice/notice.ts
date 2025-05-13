class Notice implements NoticeInterface {
    private errorNotice: string;
    private successNotice: string;
    private errorNoticeTarget: string = '[data-js-frontend-form-error-notice]';
    private successNoticeTarget: string = '[data-js-frontend-form-success-notice]';
    private messageReplaceTarget: string = '{MESSAGE_PLACEHOLDER}';
    constructor(private formContainer: HTMLElement) {
        this.errorNotice = this.extractNotice(this.errorNoticeTarget);
        this.successNotice = this.extractNotice(this.successNoticeTarget);
    }

    getErrorNotice(message: string): HTMLElement {
        const div = document.createElement('div');
        const errorNotice = this.errorNotice.replace(this.messageReplaceTarget, message);
        div.innerHTML = errorNotice;

        return div;
    }

    getSuccessNotice(message: string): HTMLElement {
        const div = document.createElement('div');
        const successNotice = this.successNotice.replace(this.messageReplaceTarget, message);
        div.innerHTML = successNotice;

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