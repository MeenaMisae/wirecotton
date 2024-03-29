<?php

namespace App\Livewire\Admin\Products\Create\Form;

use Livewire\Component;
use Livewire\WithFileUploads;

class AdminCreateProductImageForm extends Component
{
    use WithFileUploads;
    public $files = [];

    public function removeImage($imageID): void
    {
        unset($this->files[$imageID]);
        $this->files = array_values($this->files);
    }

    public function nextStep(): void
    {
        $fileUrls = [];
        foreach ($this->files as $file) :
            $fileUrls[] = $file->temporaryUrl();
        endforeach;

        $this->dispatch('product_images', $fileUrls)->to(AdminCreateProductReviewForm::class);
    }

    public function render()
    {
        return view('livewire.admin.products.create.form.admin-create-product-image-form');
    }
}
