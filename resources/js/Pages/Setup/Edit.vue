<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="w-6/12 mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="update" novalidate autocomplete="off">

                        <!-- Name -->
                        <div class="mt-4">
                            <breeze-label for="name" field="name" :value="this.__('Name') + ' *'" />

                            <breeze-input id="name" field="name" class="block mt-1 w-full" type="text" name="name" v-model="form.name" autofocus required />
                        </div>

                        <!-- Email -->
                        <div class="mt-4">
                            <breeze-label for="email" field="email" :value="this.__('Email') + ' *'" />

                            <breeze-input id="email" field="email" class="block mt-1 w-full" type="text" name="email" v-model="form.email" placeholder="ex: root.example.com" required />
                        </div>

                        <!-- expire -->
                        <div class="mt-4">
                            <breeze-label for="expire" field="expire" :value="this.__('Expire') + ' *'" />

                            <breeze-input id="expire" field="expire" class="block mt-1 w-full" type="text" name="expire" v-model="form.expire" placeholder="ex: 1w" required />
                        </div>

                        <!-- host -->
                        <div class="mt-4">
                            <breeze-label for="host" field="host" :value="this.__('Host') + ' *'" />

                            <breeze-input id="host" field="host" class="block mt-1 w-full" type="text" name="host" v-model="form.host" placeholder="ex: NAME.list.example.com" required />
                        </div>

                        <!-- list -->
                        <div class="mt-4">
                            <breeze-label for="list" field="list" :value="this.__('List') + ' *'" />

                            <breeze-input id="list" field="list" class="block mt-1 w-full" type="text" name="list" v-model="form.list" placeholder="ex: /var/lib/rbldns/NAME/NAME.list" required />
                        </div>

                        <!-- minttl -->
                        <div class="mt-4">
                            <breeze-label for="minttl" field="minttl" :value="this.__('minttl') + ' *'" />

                            <breeze-input id="minttl" field="minttl" class="block mt-1 w-full" type="text" name="minttl" v-model="form.minttl" placeholder="ex: 1m" required />
                        </div>

                        <!-- nss -->
                        <div class="mt-4">
                            <breeze-label for="nss" field="nss" :value="this.__('nss') + ' *'" />

                            <breeze-input id="nss" field="nss" class="block mt-1 w-full" type="text" name="nss" v-model="form.nss" placeholder="ex: list.example.com" required />
                        </div>

                        <!-- primaryns -->
                        <div class="mt-4">
                            <breeze-label for="primaryns" field="primaryns" :value="this.__('primaryns') + ' *'" />

                            <breeze-input id="primaryns" field="primaryns" class="block mt-1 w-full" type="text" name="primaryns" v-model="form.primaryns" placeholder="ex: list.example.com" required />
                        </div>

                        <!-- refresh -->
                        <div class="mt-4">
                            <breeze-label for="refresh" field="refresh" :value="this.__('refresh') + ' *'" />

                            <breeze-input id="refresh" field="refresh" class="block mt-1 w-full" type="text" name="refresh" v-model="form.refresh" placeholder="ex: 1h" required />
                        </div>

                        <!-- retry -->
                        <div class="mt-4">
                            <breeze-label for="retry" field="retry" :value="this.__('retry') + ' *'" />

                            <breeze-input id="retry" field="retry" class="block mt-1 w-full" type="text" name="retry" v-model="form.retry" placeholder="ex: 5m" required />
                        </div>

                        <!-- soansttl -->
                        <div class="mt-4">
                            <breeze-label for="soansttl" field="soansttl" :value="this.__('soansttl') + ' *'" />

                            <breeze-input id="soansttl" field="soansttl" class="block mt-1 w-full" type="text" name="soansttl" v-model="form.soansttl" placeholder="ex: 1w" required />
                        </div>


                        <div class="flex items-center justify-between mt-4">
                            <inertia-link class="btn btn-primary" :href="this.route('setup.index')">{{ this.__('Back') }}</inertia-link>

                            <button @click="$event.target.blur();" type="submit" class="btn btn-success" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ this.__('Save') }}
                            </button>
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
    import BreezeButton from '@/Components/Button'
    import BreezeInput from '@/Components/Input'
    import BreezeLabel from '@/Components/Label'

    export default {
        components: {
            BreezeAuthenticatedLayout,
            BreezeButton,
            BreezeInput,
            BreezeLabel
        },

        props: [
            'list',
            'isEdit'
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
                    name: this.isEdit ? this.list.name : null,
                    email: this.isEdit ? this.list.email : null,
                    expire: this.isEdit ? this.list.expire : null,
                    host: this.isEdit ? this.list.host : null,
                    list: this.isEdit ? this.list.list : null,
                    minttl: this.isEdit ? this.list.minttl : null,
                    nss: this.isEdit ? this.list.nss : null,
                    primaryns: this.isEdit ? this.list.primaryns : null,
                    refresh: this.isEdit ? this.list.refresh : null,
                    retry: this.isEdit ? this.list.retry : null,
                    soansttl: this.isEdit ? this.list.soansttl : null
                })
            }
        },

        methods: {
            focusError() {
                let el = document.querySelector("input.border-red-700");

                el.focus();
            },
            update() {
                if (this.isEdit) {
                    this.form.put(this.route('setup.update', this.list.id), {
                        onError: () => {
                            this.focusError();
                        }
                    });
                } else {
                    this.form.post(this.route('setup.store'), {
                        onError: () => {
                            this.focusError();
                        }
                    });
                }
            }
        }
    }
</script>
