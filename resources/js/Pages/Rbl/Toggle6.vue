<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="col-12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="mb-5">{{ this.__('['+field+']') }} {{ cidrInfo }} {{ this.__('[in]') }} {{ list }}</h2>

                        <form @submit.prevent="update" novalidate autocomplete="off">
                            <input type="hidden" name="redirectUrl" v-model="form.redirectUrl">

                            <div class="form-group">
                                <label for="reason">{{ this.__('[Reason]') }} {{ this.__('['+field+']') }}</label>

                                <textarea autofocus class="form-control" id="reason" name="reason" rows="3" v-model="form.reason"></textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-success">{{ this.__('[Toggle]')}} {{ this.__('['+field+']') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'

export default {
    components: {
        BreezeAuthenticatedLayout,
    },

    props: [
        'id',
        'field',
        'cidrInfo',
        'list',
        'reason',
        'referer'
    ],
    mounted() {
        let input = document.querySelector('[autofocus]');
        if (input) {
            input.focus()
        }
    },
    data() {
        return {
            form: this.$inertia.form({
                reason: this.reason,
                redirectUrl: this.referer
            })
        }
    },
    methods: {
        update() {
            let url = this.route('update.toggle6', {
                'id': this.id,
                'list': this.list,
                'field': this.field
            });

            this.form.post(url);
        }
    }
}
</script>
