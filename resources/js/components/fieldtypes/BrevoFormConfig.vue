<script setup>
import { Fieldtype } from '@statamic/cms';
import {
    PublishFields as Fields,
    PublishFieldsProvider as FieldsProvider,
} from '@statamic/cms/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);

const fieldPathPrefix = computed(() => {
    if (props.fieldPathPrefix) {
        return `${props.fieldPathPrefix}.${props.handle}`;
    }

    return props.handle;
});

const metaPathPrefix = computed(() => {
    if (props.metaPathPrefix) {
        return `${props.metaPathPrefix}.${props.handle}`;
    }

    return props.handle;
});

defineExpose(expose);
</script>

<template>
    <FieldsProvider
        :fields="config.fields"
        :field-path-prefix
        :meta-path-prefix
    >
        <Fields />
    </FieldsProvider>
</template>
