import BrevoFormConfig from './components/fieldtypes/BrevoFormConfig.vue';

Statamic.booting(() => {
    Statamic.component('brevo_form_config-fieldtype', BrevoFormConfig);
});
