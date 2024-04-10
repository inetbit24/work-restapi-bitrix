<?php

/*
|--------------------------------------------------------------------------
| Функции
|--------------------------------------------------------------------------
|
| Общие функции для работы
|
*/

if (!function_exists('dump')) {
    /**
     * Выводим дебаг результат
     *
     * @param  mixed  $param
     */
    function dump(mixed $param, string $color = '')
    {
        if (!defined('XRON_CONSOLE')) {
            echo "<pre>";
            print_r($param);
            echo "</pre>";
        } else
            Xron\Console\Console::dump($param, $color);
    }
}

/*
|--------------------------------------------------------------------------
| Функции
|--------------------------------------------------------------------------
|
| Общие функции для работы
|
*/

if (!function_exists('dd')) {
    /**
     * Выводим дебаг результат
     *
     * @param  mixed  $param
     */
    function dd(mixed $param)
    {
        dump($param);
        die();
    }
}

/*
| Функция рекурсивного чтения файлов
*/

if (!function_exists('list_all_files')) {
    /**
     * Функция рекурсивного чтения файлов
     *
     * @param  string  $dir
     */
    function list_all_files(string $dir, array $filter = []): array
    {
        $array = array();
        $new_dir = null;
        $dir_files = opendir($dir);

        while (false !== ($file = readdir($dir_files))) {
            if ($file != '.' && $file != '..')
                $new_dir[] = $dir . "/" . $file;
        }

        if ($new_dir) {
            foreach ($new_dir as $check) {
                if (is_file($check)) {
                    $array[] = $check;
                } else if (is_dir($check)) {
                    $array = array_merge($array, list_all_files($check));
                }
            }
        }

        return $array;
    }
}

/*
| Функция рекурсивного чтения файлов
*/

if (!function_exists('list_all_dir')) {
    /**
     * Функция рекурсивного чтения файлов
     *
     * @param  string  $dir
     */
    function list_all_dir(string $dir, array $filter = []): array
    {
        $array = array();
        $new_dir = null;
        $dir_files = opendir($dir);

        while (false !== ($file = readdir($dir_files))) {
            if ($file != '.' && $file != '..')
                $new_dir[] = $dir . "/" . $file;
        }

        if ($new_dir) {
            foreach ($new_dir as $check) {
                if (is_dir($check)) {
                    $array[] = $check;
                }
            }
        }

        return $array;
    }
}

/*
| Получить количество слешей в урле
|
*/

if (!function_exists('count_slash')) {
    /**
     * Получить количество слешей в урле
     *
     * @param  string  $url
     */
    function count_slash(string $url)
    {
        return count(explode('/', $url)) - 1;
    }
}

if (!function_exists('base_path()')) {
    /**
     * Получить путь корневой ветки
     *
     */
    function base_path()
    {
        return Xron\Kernel\Run::getPathRoot();
    }
}

/*
| Сравнение двух урлов на идентичность
|
*/

if (!function_exists('equallyUrl')) {
    /**
     * Сравнение двух урлов на идентичность
     *
     * @param  string  $url
     * @param  string  $equally_url
     */
    function equallyUrl(string $url, string $equally_url)
    {
        $isEqually = true;

        $lsUrl = explode('/', $url);
        $lsEquallyUrl = explode('/', $equally_url);
        unset($lsUrl[0]);
        unset($lsEquallyUrl[0]);

        foreach ($lsUrl as $k => $v) {
            if (empty($v))
                unset($lsUrl[$k]);
        }

        foreach ($lsEquallyUrl as $k => $v) {
            if (empty($v))
                unset($lsEquallyUrl[$k]);
        }

        return count($lsUrl) == count($lsEquallyUrl);
    }
}

if (!function_exists('response')) {
    /**
     * Получите оцененное содержимое представления для данного представления.
     *
     * @param  array|string|bool|int|Closure  $data - само тело запроса в виде массива
     * @param  int  $status - статус ответа
     * @param  array  $headers - заголовки ответа 
     * @return App\Providers\Response\ResponseProvider
     */
    function response(mixed $data = null, int $status = 0, array $headers = [])
    {
        return new App\Providers\Response\ResponseProvider($data, $status, $headers);
    }
}

if (!function_exists('flatten')) {
    /**
     * Привести массив к одному уровню
     *
     * @param  iterable  $array
     * @param  int  $depth
     * @return array
     */
    function flatten($array, $depth = 0)
    {
        $result = [];

        foreach ($array as $item) {

            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }
}

if (!function_exists('normalizerArray')) {
    /**
     * Нормализация массива где ключ начинается с 0 и т.д.
     *
     * @param  array $array
     * @return array
     */
    function normalizerArray($array): array
    {
        $result = [];

        foreach ($array as $item) {
            $result[] = $item;
        }

        return $result;
    }
}

if (!function_exists('get_columns_by_key')) {
    /**
     * Получаем значения нужных полей массива
     *
     * @param  array  $array
     * @param  array  $columns
     * @return array
     */
    function get_columns_by_key($array, $columns = ['id'])
    {
        $result = [];

        foreach ($columns as $column) {
            foreach ($array as $k => $item) {
                if (isset($item[$column]))
                    $result[$column][$item[$column]] = $item[$column];
            }
        }

        return $result;
    }
}

if (!function_exists('merge_array_by_object')) {
    /**
     * Сливаем массивы дочерние в основной родительский массив
     *
     * @param  array  $merge_array
     * @param  string $pivot
     * @param  array  $array_data
     * @return array
     */
    function merge_array_by_object(&$merge_array, $array_data)
    {
        foreach ($merge_array as $kma => &$vma) {
            foreach ($array_data as $kad => $vad) {
                $pivot = $vad['column'];
                foreach ($vad['builder'] as $kadv => $vadv) {
                    if ($vma[$pivot['localKey']] == $vadv[$pivot['foreignKey']])
                        $vma[$kad][] = $vadv;
                }
            }
        }
    }
}

if (!function_exists('search_domain')) {
    /**
     * Получаем домен - поддомен 
     * . - является главнфм доменом
     *
     * @param  string  $http_host
     * @return string
     */
    function search_domain($http_host)
    {
        if (!empty($http_host) && $http_host !== $_SERVER['HTTP_HOST']) {
            $tmp = explode('.', $http_host);

            unset($tmp[count($tmp) - 1], $tmp[count($tmp) - 1]);

            if (isset($tmp[0]) && $tmp[0] != 'www')
                return $tmp[0];
            else
                return '.';
        }

        return '.';
    }
}


if (!function_exists('replaces')) {
    /**
     * Зкменяем нужными значениями
     *
     * @param  string  $subject - строка в которой будем заменять
     * @param  array  $ar - массив для замены в строке
     * @return string
     */
    function replaces($subject, $ar)
    {
        $search = array();
        $replace = array();
        foreach ($ar as $k => $v) {
            $search[] = $k;
            $replace[] = $v;
        }

        return str_replace($search, $replace, $subject ?? '');
    }
}

if (!function_exists('clear_array_of_empty_values_unique')) {
    /**
     * Удаляем пустые значения в массиве такие как '', ' ', null, 0, array()
     * и очищаем массив от повторяющихся значений
     *
     * @param  array  $array
     * @return array
     */
    function clear_array_of_empty_values_unique(array $array): array
    {
        $newArray = [];
        foreach ($array as $val) {
            if (empty($val))
                continue;
            $newArray[] = $val;
        }
        return array_unique($newArray);
    }
}

if (!function_exists('search_value_many_array')) {
    /**
     * Ищем в массиве данные от одного улюча по другому значению
     * и очищаем массив от повторяющихся значений
     *
     * @param  array  $arrayData
     * @param  array  $arraySearch
     * @param  array  $arraySearchMerge
     * @return array
     */
    function search_value_many_array(array $arrayData, array $arraySearch, array $arraySearchMerge): array
    {
        $res = [];
        foreach ($arraySearch as $keySearch => $valSearch) {
            $value = $valSearch['value'];
            $key = $valSearch['key'];
            $filter = current(array_filter($arrayData[$keySearch], function ($v, $k) use ($value, $key) {
                return $v[$key] == $value;
            }, ARRAY_FILTER_USE_BOTH));

            $search = $filter['xml_id'];
            $filter = current(array_filter($arraySearchMerge[$keySearch], function ($v, $k) use ($search) {
                return $v['docdoc_id'] == $search;
            }, ARRAY_FILTER_USE_BOTH));

            $res[$keySearch] = $filter;
        }

        return $res;
    }
}

if (!function_exists('convert_array_key_value')) {
    /**
     * Конвертируем массив ключ/значение
     *
     * @param  array  $arrayData
     * @param  string  $key
     * @return array
     */
    function convert_array_key_value(array $arrayData, string $key): array
    {
        $res = [];
        foreach ($arrayData as $val) {
            $res[$val[$key]] = $val;
        }
        return $res;
    }
}

if (!function_exists('filter_array_column')) {
    /**
     * Поиск массив ключ/значение
     *
     * @param  string  $column - по этой колонке будет происходить поиск
     * @param  array  $search
     * @param  array  $data
     * @return array
     */
    function filter_array_column(string $column, array $search, array $data): array
    {
        return array_filter($data, function ($v, $k) use ($search, $column) {
            return array_search($v[$column], $search) !== false;
        }, ARRAY_FILTER_USE_BOTH);
    }
}

if (!function_exists('filter_array_column_like')) {
    /**
     * Поиск в массиве по частичному совпадению значений или по выбранному типу сравнения
     *
     * @param  string  $column - по этой колонке будет происходить поиск
     * @param  string  $search - искомое значение
     * @param  array  $data - массив со списком значений для поиска
     * @param  int $type - типы сравнения (0 - сравнение %text% , 1 - сравнение %text% с учетом регистра, 2 - строгое сравнение)
     * 3 - сравнение %text% в обратном порядке, 4 - сравнение %text% с учетом регистра в обратном порядке
     * @return array
     */
    function filter_array_column_like(string $column, string $search, array $data, int $type = 0): array
    {
        return array_filter($data, function ($v, $k) use ($search, $column, $type) {
            if ($type === 0)
                return mb_stripos($v[$column], $search) !== false;
            if ($type === 1)
                return strpos($v[$column], $search) !== false;
            if ($type === 2)
                return $v[$column] === $search;
            if ($type === 3)
                return mb_stripos($search, $v[$column]) !== false;
            if ($type === 4)
                return strpos($search, $v[$column]) !== false;
        }, ARRAY_FILTER_USE_BOTH);
    }
}

if (!function_exists('get_array_value_is_key')) {
    /**
     * Получим значение массива по его ключам, разделенными через точку
     * ключ key1.key2.key3
     * значение values[key1][key2][key3]
     * 
     * @param  array  $values - ассив значений
     * @param  string  $keys - ключи разделенный через точку
     * @return mixed|array
     */
    function get_array_value_is_key(array $values, ?string $keys = null): mixed
    {
        if ($keys == null)
            return $values;
        $var = explode('.', $keys);

        if (sizeof($var) > 1) {
            if (!isset($values[$var[0]]))
                return null;

            $vars = $values[$var[0]];
            for ($i = 1; $i < sizeof($var); $i++) {
                if (!isset($vars[$var[$i]]))
                    return null;
                $vars = $vars[$var[$i]];
            }

            return $vars ?? null;
        } else
            return isset($values[$var[0]]) ? $values[$var[0]] : null;

    }
}

if (!function_exists('array_search_key')) {
    /**
     * Осуществляем поиск ключа в массиве по значению.
     *
     * @param  mixed  $search - что ищем
     * @param  array  $data - искомый массив для поиска
     * @param  string $column - по этой колонке будет происходить поиск
     * @return array
     */
    function array_search_key(mixed $search = null, ?array $data = null, ?string $column = null): mixed
    {
        $key = false;
        if ($data)
            foreach ($data as $k => $v) {
                if ($column !== null) {
                    if ($v[$column] == $search)
                        return $k;
                } else if ($v == $search)
                    return $k;
            }
        return $key;
    }
}

if (!function_exists('clear_string')) {
    /**
     * Осуществляем чистку строки от знаков ! ( ) % 
     *
     * @param  string  $clearString - очищаемая строка
     * @return string
     */
    function clear_string(?string $clearString = null): string
    {
        return preg_replace('/[^0-9a-zA-Z]/', '', $clearString);
    }
}

if (!function_exists('format_bool')) {
    /**
     * Преобразует логическое значение bool из строки в bool
     *
     * @param  string  $clearString - очищаемая строка
     * @return string
     */
    function format_bool(mixed $bool): bool
    {
        if (gettype($bool) === 'string' && !is_numeric($bool))
            return $bool === 'true';

        return (int) $bool;
    }
}

if (!function_exists('array_cast_recursive')) {
    /**
     * рекурсивный обход stdClass превращая его в массив
     *
     * @param  stdClass | array $object
     * @return array
     */
    function array_cast_recursive(stdClass | array $object): array
    {
        if (is_array($object)) {
            foreach ($object as $key => $value) {
                if (is_array($value)) {
                    $object[$key] = array_cast_recursive($value);
                }
                if ($value instanceof stdClass) {
                    $object[$key] = array_cast_recursive((array) $value);
                }
            }
        }
        if ($object instanceof stdClass) {
            return array_cast_recursive((array) $object);
        }

        return $object;
    }
}