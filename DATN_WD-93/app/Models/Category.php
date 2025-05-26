<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'img', 'status']; //Thuộc tính fillable khai báo các cột trong bảng categories mà có thể được gán giá trị một cách hàng loạt
    //khai báo thuộc tính $fillable trong model không bắt buộc, nhưng rất quan trọng để bảo vệ ứng dụng của bạn khỏi các cuộc tấn công loại Mass Assignment (Gán hàng loạt).
    //Mass Assignment: Là quá trình gán giá trị cho nhiều thuộc tính của một model cùng một lúc bằng cách truyền một mảng dữ liệu. Ví dụ:
    //Category::create(['name' => 'Electronics', 'description' => 'All electronic items']);

    protected $cast = [
        'status' => 'boolean',
    ];


    //dn khoa chinh/khoa ngoai
    public function products()
    {
        return $this->hasMany(Product::class); // thiết lập mối quan hệ một-nhiều (one-to-many) giữa bảng categories và bảng products.
    }
}
