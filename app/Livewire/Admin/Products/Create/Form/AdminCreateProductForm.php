<?php

namespace App\Livewire\Admin\Products\Create\Form;

use Livewire\Component;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;

class AdminCreateProductForm extends Component
{
    public int $productDiscount = 0;
    public bool $onSale = false;
    public $productFinalPrice;
    #[Validate('required|numeric|gt:0', as: 'preço', message: ['productPrice.required' => 'obrigatório'])]
    public $productPrice;
    #[Validate('required|integer|min:1', as: 'quantidade', message: ['productQuantity.required' => 'obrigatório'])]
    public $productQuantity;
    #[Validate('required|string|min:3', as: 'produto')]
    public $productName;
    #[Validate('nullable|string|min:3|max:200', as: 'descrição')]
    public $productDescription;
    #[Validate('required|exists:categories,id', as: 'categoria')]
    public $categoryID = 0;
    #[Validate('required|exists:subcategories,id', as: 'subcategoria')]
    public $subcategoryID = 0;
    public Collection $categories;
    public $subcategories = [];

    public function messages(): array
    {
        return [
            'subcategoryID.exists' => 'subcategoria inválida.',
            'categoryID.exists' => 'categoria inválida.',
            'productDiscount' => 'desconto inválido',
            'productName.min' => 'tamanho mínimo de 3 caracteres'
        ];
    }

    public function rules(): array
    {
        $rules = [];
        if ($this->onSale) :
            $rules['productDiscount'] = 'required|integer|gt:0';
        endif;

        return $rules;
    }

    public function mount(): void
    {
        $this->categories = Category::all();
    }

    public function calculateDiscount(): void
    {
        if (empty($this->productPrice)) :
            return;
        endif;
        $final = $this->productPrice - $this->productPrice * ($this->productDiscount / 100);
        $this->productFinalPrice = number_format($final, 2, ',', '.');
    }

    public function loadSubcategories(): void
    {
        $this->subcategories = Subcategory::where('category_id', '=', $this->categoryID)->get();
        if (count($this->subcategories)) :
            $this->subcategoryID = Subcategory::where('category_id', '=', $this->categoryID)->first()->id;
        endif;
    }

    public function validateStep()
    {
        $this->validate();
        $this->dispatch('step_validated');
    }
    public function nextStep(): void
    {
        $data = [
            'category_id' => $this->categoryID,
            'subcategory_id' => $this->subcategoryID,
            'original_price' => is_numeric($this->productPrice) ? number_format($this->productPrice, 2, ',', '.') : $this->productPrice,
            'final_price' => $this->onSale ? $this->productFinalPrice : $this->productPrice,
            'name' => $this->productName,
            'description' => $this->productDescription,
            'slug' => Str::slug($this->productName),
            'quantity' => $this->productQuantity,
            'on_sale' => $this->onSale,
            'in_stock' => $this->productQuantity > 5 ? true : false,
            'discount' => $this->onSale ? $this->productDiscount / 100 : 0,
        ];
        $this->dispatch('create_product_form', $data)->to(AdminCreateProductReviewForm::class);
    }

    public function render()
    {
        return view('livewire.admin.products.create.form.admin-create-product-form', [
            'categories' => $this->categories,
        ]);
    }
}
