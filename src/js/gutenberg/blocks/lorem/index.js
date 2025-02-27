/**
 * External dependencies
 */
import { loremIpsum } from 'react-lorem-ipsum';

/**
 * WordPress dependencies
 */
const {
    i18n: { __ },
    blocks: { createBlock },
    data: { select, dispatch },
    blockEditor: { store: blockEditorStore },
} = wp;

/**
 * Block constants
 */
const name = 'wca/lorem';

const settings = {
    title: __('Lorem Dev Tool', 'wecodeart'),
    icon: 'admin-tools',
    description: __('Create lorem ipsum placeholders.', 'wecodeart'),
    attributes: {
        items: {
            type: 'number',
            default: 1,
        },
    },
    supports: {
        inserter: false,
    },
    transforms: {
        from: [
            {
                type: 'prefix',
                prefix: ':lorem',
                transform() {
                    return createBlock('core/paragraph', { content: loremIpsum() });
                },
            },
            ...[2, 3, 4, 5, 6, 7, 8, 9, 10].map((columns) => ({
                type: 'prefix',
                prefix: Array(columns + 1).join(':') + 'lorem',
                transform() {
                    const toSelect = [];
                    const blockIndex = select(blockEditorStore).getBlockInsertionPoint();
                    const selectedBlock = select(blockEditorStore).getSelectedBlockClientId();

                    for (let i = 1; i <= columns; i++) {
                        const created = createBlock('core/paragraph', { content: loremIpsum() });

                        dispatch(blockEditorStore).insertBlocks(created, (parseInt(blockIndex.index) + i) - 1);

                        if (typeof created !== 'undefined') {
                            toSelect.push(created.clientId);
                        }
                    }

                    dispatch(blockEditorStore).removeBlock(selectedBlock);

                    return dispatch(blockEditorStore).multiSelect(toSelect[0], toSelect.reverse()[0]);
                },
            })),
            {
                type: 'prefix',
                prefix: ':hlorem',
                transform() {
                    return createBlock('core/heading', {
                        content: loremIpsum({ avgSentencesPerParagraph: 1, startWithLoremIpsum: false, avgWordsPerSentence: 6 })[0].split('.')[0],
                    });
                },
            },
            ...[2, 3, 4, 5, 6, 7, 8, 9, 10].map((columns) => ({
                type: 'prefix',
                prefix: Array(columns + 1).join(':') + 'hlorem',
                transform() {
                    const toSelect = [];
                    const blockIndex = select(blockEditorStore).getBlockInsertionPoint();
                    const selectedBlock = select(blockEditorStore).getSelectedBlockClientId();

                    for (let i = 1; i <= columns; i++) {
                        const created = createBlock('core/heading', {
                            content: loremIpsum({ avgSentencesPerParagraph: 1, startWithLoremIpsum: false, avgWordsPerSentence: 6 })[0].split('.')[0],
                        });

                        dispatch(blockEditorStore).insertBlocks(created, (parseInt(blockIndex.index) + i) - 1);

                        if (typeof created !== 'undefined') {
                            toSelect.push(created.clientId);
                        }
                    }

                    dispatch(blockEditorStore).removeBlock(selectedBlock);

                    return dispatch(blockEditorStore).multiSelect(toSelect[0], toSelect.reverse()[0]);
                },
            })),
        ],
    },
    edit() {
        return null;
    },
    save() {
        return null;
    },
};

export default { name, settings };