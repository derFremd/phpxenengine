<?php

require_once __DIR__ . '/../classes/init.php';

use PHPXenEngine\Template\TemplateFileLoader as TemplateFileLoader;
use PHPXenEngine\Template\VisualBlock as VisualBlock;
use PHPXenEngine\Template\StringLoader as StringLoader;
use PHPXenEngine\Template\VisualBlockCallBack as VisualBlockCallBack;

// StringLoader interface implementation
$strLoader = new class() implements StringLoader {
    private array $strings = [
        'text_0' => 'This is string0',
        'text_1' => 'This is string1',
        'text_2' => 'This is string2'
    ];

    public function getStr($str_id): ?string
    {
        return $this->strings[$str_id] ?? null;
    }
};
// Sets template directly by string
$vBlock = new VisualBlock('<p>This simple template from string.</p>');

$vBlock->out();

// Path where the template files are placed.
$path = realpath(__DIR__ . '/../tpl/samples');

// Error message when template file is not found
$vBlock->setTemplate(new TemplateFileLoader('TestAbsentTemplate', $path));

$vBlock->out();

// Adds real template from file
$vBlock->setTemplate(new TemplateFileLoader('VisualBlockSample', $path));

// Adds possibility get string constant from external source StringLoader
$vBlock->setStrLoader($strLoader);

// Adds possibility get data from external variables
$vBlock->var_1 = 'This is a string variable'; // string variable
$vBlock->var_2 = [-99.3, 100.3, 0]; // number variable
$vBlock->var_3 = '$text_1'; // links to string constant '$text_1', first char should be '$'
$vBlock->var_4 = '$text_3'; // links to missing string constant '$text_3'
$vBlock->var_5 = new class {
    public function __toString(): string { return 'this is object as variable';}
};
$vBlock->var_6 = 1;
$vBlock->var_6++; // test increment
$vBlock->var_7 = true;

// array of vars
$var_array = [
    'form'=>[
        'virtuality'=>['name'=>'mr. Anderson', 'profession'=>'Programmer', 'residence'=>'Matrix'],
        'reality'=>['name'=>'Neo', 'profession'=>'the One', 'residence'=>'Nebuchadnezzar']
    ]
];
$vBlock->setVars($var_array);
// change variable after setVars()
$vBlock->form_reality_name = 'Trinity';

// Includes external file.
// See also 'TemplateFileLoader::EXT_FILES' for enabled file extensions.
$vBlock->setVarFile('file_id', 'IncludedFile');


// Includes external block with own dynamic content
$vBlock->subBlock = (new VisualBlock('This is content of external \'subBlock\' as variable: subBlockVar={{VAR:subBlockVar}}'))
    ->setVar('subBlockVar', 'Value of subBlockVar');

// Disabled block
$vBlock->disabledBlock = new VisualBlock('This content of disabled block will not be shown.');
$vBlock->disabledBlock->disable();

// Prefix and suffix of the block
$vBlock->surroundedBlock = new VisualBlock(' This is surroundedBlock. ');
$vBlock->surroundedBlock->setPrefix(new VisualBlock('[This is a prefix by VisualBlock]'));
$vBlock->surroundedBlock->setSuffix('[This is a simple string suffix]');

// Sample possibilities:
// - block repeat
// - pointer shift of the array variables

// Block with repeat mode and enabled array pointer shift
$vBlock->subBlock2 = new VisualBlock(' {{VAR:array_item}}{{VAR:array_item2}} ');
$vBlock->subBlock2->array_item = [1,2,3,4,5];
$vBlock->subBlock2->array_item[]=5; // add new array item
$vBlock->subBlock2->array_item[5]++; // increment new array item
// Shifts array pointer 2 times. Now current value is '3'.
VisualBlock::setVarArrayIndex($vBlock->subBlock2->array_item,2);
$vBlock->subBlock2->array_item2 = [';', ',']; // Second array
$vBlock->subBlock2->setCounter(6);
$vBlock->subBlock2->enableShiftArrayPointer();
$vBlock->subBlock2->setPrefixSuffix('<<<<<<', (new VisualBlock('>'))->setCounter(6));

// Block with repeat mode and disabled array pointer shift (by default)
$vBlock->subBlock3 = new VisualBlock(' {{VAR:array_item}} ');
$vBlock->subBlock3->array_item = ['KeyOne'=>'One','KeyTwo'=>'Two','KeyThree'=>'Three'];
VisualBlock::setVarArrayIndex($vBlock->subBlock3->array_item, 'KeyTwo');
$vBlock->subBlock3->setCounter(5);
$vBlock->subBlock3->setPrefixSuffix('#','#');

// Sample callBack function
$vBlock->subBlock4 = new VisualBlock('{{VAR:variable}}{{VAR:separator}}');
$vBlock->subBlock4->variable = -10;
$vBlock->subBlock4->separator = ', ';
$vBlock->subBlock4->setCounter(21);
$vBlock->subBlock4->setCallback(
    new class implements VisualBlockCallBack {
        public function callback(VisualBlock $block, int $iteration): bool {
            if($iteration > 1) $block->variable++;

            if($block->variable % 2) return false;

            if($iteration == $block->getCounter()) {
                $block->separator = '.';
            }
            return true;
        }
    }
);

// makes internal sub-blocks (inside same template).
$vBlock->makeSubBlocks();
$vBlock->subBlock5->blockName = $vBlock->subBlock5->getName();
$vBlock->subBlock5->subSubBlock->blockName = $vBlock->subBlock5->subSubBlock->getName();

// starts parsing
$vBlock->out();

