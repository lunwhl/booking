class Errors {
    /**
     * Create a new Errors instance.
     */
    constructor() {
        this.errors = {};
    }


    /**
     * Determine if an errors exists for the given field.
     *
     * @param {string} field
     */
    has(field) {
        return this.errors.hasOwnProperty(field);
    }


    /**
     * Determine if we have any errors.
     */
    any() {
        return Object.keys(this.errors).length > 0;
    }


    /**
     * Retrieve the error message for a field.
     *
     * @param {string} field
     */
    get(field) {
        if (this.errors[field]) {
            return this.errors[field][0];
        }
    }


    /**
     * Record the new errors.
     *
     * @param {object} errors
     */
    record(errors) {
        this.errors = errors.errors;
    }


    /**
     * Clear one or all error fields.
     *
     * @param {string|null} field
     */
    clear(field) {
        if (field) {
            delete this.errors[field];

            return;
        }

        this.errors = {};
    }
}


class Form {
    /**
     * Create a new Form instance.
     *
     * @param {object} data
     */
    constructor(data) {
        this.originalData = data;

        for (let field in data) {
            this[field] = data[field];
        }

        this.errors = new Errors();

        this.submitting = false;
    }


    /**
     * Fetch all relevant data for the form.
     */
    data() {
        let data = new FormData();

        for (let property in this.originalData) {

            let value = this[property];

            if(Array.isArray(this[property]))
                value = JSON.stringify(this[property]);

            if(this[property])
                data.append(property, value);
        }

        return data;
    }


    /**
     * Reset the form fields.
     */
    reset() {
        for (let field in this.originalData) {
            this[field] = this.originalData[field];
        }
        this.submitting = false;
        this.errors.clear();
    }


    /**
     * Send a POST request to the given URL.
     * .
     * @param {string} url
     */
    post(url, clear = true) {
        return this.submit('post', url, clear);
    }


    /**
     * Send a PUT request to the given URL.
     * .
     * @param {string} url
     */
    put(url) {
        return this.submit('put', url);
    }


    /**
     * Send a PATCH request to the given URL.
     * .
     * @param {string} url
     */
    patch(url) {
        return this.submit('patch', url);
    }


    /**
     * Send a DELETE request to the given URL.
     * .
     * @param {string} url
     */
    delete(url) {
        return this.submit('delete', url);
    }


    /**
     * Submit the form.
     *
     * @param {string} requestType
     * @param {string} url
     */
    submit(requestType, url, clear = true) {
        if(!this.submitting)
        {
            this.submitting = true;


            return new Promise((resolve, reject) => {
                axios[requestType](url, this.data())
                    .then(response => {
                        this.onSuccess(response.data, clear);

                        resolve(response.data);
                    })
                    .catch(error => {
                        console.log(error);
                        this.onFail(error.response);
                        window.event.$emit("loading", {'openDialog': false});
                        reject(error.response.data);
                    });
            });
        }
        return new Promise((resolve, reject) => {
            this.submitting = false;
            reject();


        });
    }


    /**
     * Handle a successful form submission.
     *
     * @param {object} data
     */
    onSuccess(data, clear = true) {
        //flash(data.message);
        if(clear)
            this.reset();

        this.submitting = false;
    }


    /**
     * Handle a failed form submission.
     *
     * @param {object} errors
     */
    onFail(errors) {
        let error = 'Oops! Something went wrong. Please check your input.';

        if(errors.status == 500) { error = 'Something went wrong, please try again'; }
        else {
            if(errors.data.message) error = errors.message;
            
            //console.log(errors);
            this.errors.record(errors.data);
        }

        //flash(error, 'danger');

        this.submitting = false;
    }
}
