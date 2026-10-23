@props([
    'name' => null,
    'modelValue' => '',
    'placeholder' => 'Write detailed description or notes here...',
    'disabled' => false,
    'minHeight' => '180px',
    'id' => null,
    'label' => '',
    'error' => '',
])

@if(!empty($name) || !empty($label) || !empty($modelValue))
<v-rich-text
    @if($name) name="{{ $name }}" @endif
    @if(!is_null($modelValue)) :model-value='@json($modelValue)' @endif
    @if($label) label="{{ $label }}" @endif
    placeholder="{{ $placeholder }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    min-height="{{ $minHeight }}"
    @if($id) id="{{ $id }}" @endif
    @if($error) error="{{ $error }}" @endif
    {{ $attributes }}
></v-rich-text>
@endif

@pushOnce('scripts')
<script type="text/x-template" id="v-rich-text-template">
    <div class="rich-text-wrapper space-y-1.5 w-full">
        <div v-if="label" class="flex items-center justify-between">
            <label class="block text-xs font-semibold text-[var(--text)]">
                @{{ label }}
            </label>
            <span v-if="disabled" class="text-[10px] uppercase font-mono text-[var(--muted)]">Read Only</span>
        </div>

        <!-- Hidden input for standard forms -->
        <input
            v-if="name"
            ref="hiddenInput"
            type="hidden"
            :id="id"
            :name="name"
            :value="modelValue"
        />

        <!-- Editor Container -->
        <div
            class="rich-text-container rounded-xl border transition-all duration-200 overflow-hidden bg-[var(--card)]"
            :class="[
                error ? 'border-red-500/80 ring-1 ring-red-500/40' : 'border-[var(--border)] focus-within:border-[var(--teal)] focus-within:ring-2 focus-within:ring-[var(--teal)]/20',
                disabled ? 'opacity-60 bg-[var(--page)] cursor-not-allowed' : 'hover:border-[var(--teal)]/60'
            ]"
        >
            <!-- Studio Toolbar -->
            <div
                v-if="!disabled && editor"
                class="rich-text-toolbar flex flex-wrap items-center gap-1 px-3 py-2 border-b border-[var(--border)] bg-[var(--page)] select-none"
            >
                <button
                    type="button"
                    @click="editor.chain().focus().toggleBold().run()"
                    :class="editor.isActive('bold') ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition-colors cursor-pointer"
                    title="Bold"
                >
                    <strong>B</strong>
                </button>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleItalic().run()"
                    :class="editor.isActive('italic') ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition-colors cursor-pointer"
                    title="Italic"
                >
                    <em>I</em>
                </button>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleStrike().run()"
                    :class="editor.isActive('strike') ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition-colors cursor-pointer line-through"
                    title="Strikethrough"
                >
                    S
                </button>

                <div class="h-4 w-px bg-[var(--border)] mx-1"></div>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                    :class="editor.isActive('heading', { level: 1 }) ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="px-2 h-7 rounded-lg flex items-center justify-center text-[11px] font-mono font-bold transition-colors cursor-pointer"
                    title="Heading 1"
                >
                    H1
                </button>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="editor.isActive('heading', { level: 2 }) ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="px-2 h-7 rounded-lg flex items-center justify-center text-[11px] font-mono font-bold transition-colors cursor-pointer"
                    title="Heading 2"
                >
                    H2
                </button>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    :class="editor.isActive('heading', { level: 3 }) ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="px-2 h-7 rounded-lg flex items-center justify-center text-[11px] font-mono font-bold transition-colors cursor-pointer"
                    title="Heading 3"
                >
                    H3
                </button>

                <div class="h-4 w-px bg-[var(--border)] mx-1"></div>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleBulletList().run()"
                    :class="editor.isActive('bulletList') ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition-colors cursor-pointer"
                    title="Bullet List"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleOrderedList().run()"
                    :class="editor.isActive('orderedList') ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition-colors cursor-pointer"
                    title="Numbered List"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
                </button>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleBlockquote().run()"
                    :class="editor.isActive('blockquote') ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition-colors cursor-pointer"
                    title="Blockquote"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/></svg>
                </button>

                <button
                    type="button"
                    @click="editor.chain().focus().toggleCodeBlock().run()"
                    :class="editor.isActive('codeBlock') ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition-colors cursor-pointer font-mono"
                    title="Code Block"
                >
                    &lt;/&gt;
                </button>

                <div class="h-4 w-px bg-[var(--border)] mx-1"></div>

                <button
                    type="button"
                    @click="setLink"
                    :class="editor.isActive('link') ? 'bg-[var(--soft)] text-[var(--teal)] font-bold' : 'text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)]'"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs transition-colors cursor-pointer"
                    title="Insert Link"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </button>

                <button
                    type="button"
                    @click="editor.chain().focus().unsetAllMarks().clearNodes().run()"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--card)] transition-colors cursor-pointer ml-auto"
                    title="Clear Formatting"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
                </button>
            </div>

            <!-- Tiptap Editor Surface -->
            <div
                ref="editorElement"
                :style="{ minHeight: minHeight }"
                class="tiptap-content p-4 text-xs text-[var(--text)] focus:outline-none overflow-y-auto"
            ></div>
        </div>

        <span v-if="error" class="block text-[11px] font-medium text-red-500">
            @{{ error }}
        </span>
    </div>
</script>

<script>
(function() {
    const RichTextComponent = {
        template: '#v-rich-text-template',
        props: {
            modelValue: {
                type: String,
                default: '',
            },
            placeholder: {
                type: String,
                default: 'Write detailed description or notes here...',
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            minHeight: {
                type: String,
                default: '180px',
            },
            name: {
                type: String,
                default: '',
            },
            id: {
                type: String,
                default: '',
            },
            label: {
                type: String,
                default: '',
            },
            error: {
                type: String,
                default: '',
            },
        },
        emits: ['update:modelValue', 'change', 'blur', 'focus'],
        data() {
            return {
                editor: null,
            };
        },
        mounted() {
            this.initEditor();
        },
        beforeUnmount() {
            if (this.editor) {
                this.editor.destroy();
            }
        },
        watch: {
            modelValue(value) {
                if (!this.editor) return;
                const isSame = this.editor.getHTML() === value;
                if (!isSame) {
                    this.editor.commands.setContent(value || '', false);
                }
            },
            disabled(val) {
                if (this.editor) {
                    this.editor.setEditable(!val);
                }
            },
        },
        methods: {
            initEditor() {
                const Tiptap = window.Tiptap;
                if (!Tiptap || !Tiptap.Editor) {
                    console.warn('Tiptap not available on window.Tiptap');
                    return;
                }

                const { Editor, StarterKit, Placeholder, Link } = Tiptap;

                this.editor = new Editor({
                    element: this.$refs.editorElement,
                    extensions: [
                        StarterKit.configure({
                            heading: {
                                levels: [1, 2, 3],
                            },
                        }),
                        Placeholder.configure({
                            placeholder: this.placeholder,
                        }),
                        Link.configure({
                            openOnClick: false,
                            HTMLAttributes: {
                                class: 'text-[var(--teal)] underline hover:opacity-80 transition-opacity',
                                target: '_blank',
                                rel: 'noopener noreferrer',
                            },
                        }),
                    ],
                    content: this.modelValue || '',
                    editable: !this.disabled,
                    onUpdate: () => {
                        const html = this.editor.getHTML();
                        const cleanHtml = html === '<p></p>' ? '' : html;
                        this.$emit('update:modelValue', cleanHtml);
                        this.$emit('change', cleanHtml);
                        if (this.$refs.hiddenInput) {
                            this.$refs.hiddenInput.value = cleanHtml;
                        }
                    },
                    onFocus: () => {
                        this.$emit('focus');
                    },
                    onBlur: () => {
                        this.$emit('blur');
                    },
                });
            },
            setLink() {
                if (!this.editor) return;
                const previousUrl = this.editor.getAttributes('link').href;
                const url = window.prompt('Enter URL:', previousUrl || 'https://');
                if (url === null) return;
                if (url === '') {
                    this.editor.chain().focus().extendMarkRange('link').unsetLink().run();
                    return;
                }
                this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
            },
        },
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-rich-text', RichTextComponent);
        window.registerVueComponent('rich-text-editor', RichTextComponent);
    } else if (window.app && window.app.component) {
        window.app.component('v-rich-text', RichTextComponent);
        window.app.component('rich-text-editor', RichTextComponent);
    }
})();
</script>
@endPushOnce
