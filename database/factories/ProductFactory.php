<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    private static array $categories = [
        'Двигатель',
        'Тормозная система',
        'Подвеска',
        'Электрика',
        'Кузов',
    ];

    private static array $productNames = [
        'Двигатель' => [
            'Масляный фильтр',
            'Воздушный фильтр',
            'Ремень ГРМ',
            'Свеча зажигания',
            'Прокладка ГБЦ',
            'Помпа охлаждения',
            'Термостат',
            'Цепь ГРМ',
            'Натяжитель цепи',
            'Маслосъёмный колпачок',
        ],
        'Тормозная система' => [
            'Тормозные колодки передние',
            'Тормозные колодки задние',
            'Тормозной диск передний',
            'Тормозной диск задний',
            'Тормозной цилиндр',
            'Тормозной шланг',
            'Суппорт тормозной',
            'Тормозная жидкость DOT 4',
            'АБС датчик',
            'Усилитель тормозов',
        ],
        'Подвеска' => [
            'Амортизатор передний',
            'Амортизатор задний',
            'Пружина подвески',
            'Стойка стабилизатора',
            'Рычаг подвески',
            'Шаровая опора',
            'Сайлентблок',
            'Подшипник ступицы',
            'ШРУС внешний',
            'ШРУС внутренний',
        ],
        'Электрика' => [
            'Аккумулятор 60Ah',
            'Генератор',
            'Стартер',
            'Реле стартера',
            'Предохранитель 30A',
            'Датчик кислорода',
            'Датчик температуры',
            'Катушка зажигания',
            'Форсунка топливная',
            'Лямбда-зонд',
        ],
        'Кузов' => [
            'Бампер передний',
            'Бампер задний',
            'Крыло переднее левое',
            'Крыло переднее правое',
            'Капот',
            'Дверь передняя левая',
            'Зеркало заднего вида',
            'Фара передняя',
            'Фонарь задний',
            'Решётка радиатора',
        ],
    ];

    public function definition(): array
    {
        $category = $this->faker->randomElement(self::$categories);
        $names = self::$productNames[$category];
        $name = $this->faker->randomElement($names);

        return [
            'name' => $name,
            'sku' => strtoupper($this->faker->unique()->bothify('??-####-??')),
            'price' => $this->faker->randomFloat(2, 100, 15000),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'category' => $category,
        ];
    }

    public function inStock(): static
    {
        return $this->state(fn() => [
            'stock_quantity' => $this->faker->numberBetween(5, 50),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn() => [
            'stock_quantity' => 0,
        ]);
    }
}
