<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table.
     */
    public function run(): void
    {
        // [tên, giá, giá khuyến mãi, tồn kho, mô tả]
        $products = [
            'hoa-sinh-nhat' => [
                ['Bó hồng phấn Sweet Day', 450000, 399000, 20, 'Hồng phấn dịu dàng phối lá bạc, gói giấy Hàn Quốc tông kem — món quà sinh nhật ngọt ngào.'],
                ['Bó hướng dương Sunshine', 380000, null, 15, 'Những đóa hướng dương rực rỡ như ánh nắng, gửi lời chúc một tuổi mới tràn đầy năng lượng.'],
                ['Giỏ hoa cúc họa mi', 320000, null, 12, 'Giỏ mây xinh xắn với cúc họa mi trắng tinh khôi, mộc mạc mà đáng yêu.'],
                ['Bó tulip Hà Lan mix', 850000, 790000, 8, 'Tulip nhập khẩu Hà Lan nhiều màu, sang trọng và hiện đại cho người đặc biệt.'],
                ['Bó cẩm chướng hồng Ngọt Ngào', 290000, null, 18, 'Cẩm chướng hồng bền màu, tượng trưng cho tình cảm chân thành và lời chúc tốt đẹp.'],
                ['Bó đồng tiền rực rỡ', 260000, null, 22, 'Hoa đồng tiền nhiều màu tươi vui, mang may mắn và niềm vui đến người nhận.'],
                ['Giỏ hồng cam Năng Lượng', 520000, 470000, 10, 'Hồng cam tươi tắn cắm giỏ tròn, thích hợp tặng bạn bè, đồng nghiệp dịp sinh nhật.'],
                ['Bó cát tường tím Pastel', 350000, null, 14, 'Cát tường tím pastel mềm mại, lâu tàn, mang ý nghĩa may mắn và bình an.'],
                ['Bó mẫu đơn hồng Kiêu Sa', 1200000, null, 5, 'Mẫu đơn hồng bung cánh lộng lẫy — biểu tượng của sự sung túc và vẻ đẹp kiêu sa.'],
                ['Hộp hoa mix Bất Ngờ', 480000, null, 12, 'Hộp hoa phối nhiều loại theo mùa, mỗi hộp là một bất ngờ nhỏ cho người nhận.'],
            ],
            'hoa-tinh-yeu' => [
                ['Bó 99 hồng đỏ Forever', 2500000, 2290000, 5, '99 đóa hồng đỏ Đà Lạt tuyển chọn — lời tỏ tình trọn vẹn cho tình yêu mãi mãi.'],
                ['Bó hồng đỏ Classic 20 bông', 650000, null, 18, 'Bó hồng đỏ cổ điển 20 bông, gói giấy đen sang trọng, chưa bao giờ lỗi mốt.'],
                ['Hộp hồng kem Love Box', 720000, 680000, 10, 'Hồng kem xếp trong hộp tròn cao cấp, tinh tế cho dịp kỷ niệm.'],
                ['Bó baby trắng tinh khôi', 350000, null, 25, 'Bó baby trắng bồng bềnh như mây, nhẹ nhàng và trong trẻo.'],
                ['Bó hồng trắng Tinh Khôi', 590000, null, 12, 'Hồng trắng thuần khiết, gửi gắm tình yêu trong sáng và chân thành.'],
                ['Bó hồng tím Thủy Chung', 690000, null, 9, 'Hồng tím mộng mơ — biểu tượng của tình yêu thủy chung, son sắt.'],
                ['Bó tulip đỏ Say Đắm', 780000, 720000, 7, 'Tulip đỏ rực, lời tuyên ngôn cho một tình yêu nồng nàn, say đắm.'],
                ['Bó hồng xanh Ocean', 890000, null, 6, 'Hồng xanh độc đáo hiếm có, dành cho người bạn thương đặc biệt nhất.'],
                ['Giỏ hồng đỏ Trái Tim', 1100000, 990000, 4, 'Hồng đỏ cắm hình trái tim trong giỏ, món quà lãng mạn cho Valentine.'],
                ['Bó hồng vàng Nắng Mai', 560000, null, 11, 'Hồng vàng tươi sáng như nắng mai, thay lời chúc hạnh phúc và tình bạn bền lâu.'],
            ],
            'hoa-khai-truong' => [
                ['Kệ hoa Hồng Phát 2 tầng', 1800000, null, 6, 'Kệ hoa hai tầng rực rỡ sắc đỏ – vàng, chúc khai trương hồng phát, buôn may bán đắt.'],
                ['Kệ hoa Đại Cát', 1500000, 1390000, 7, 'Kệ hoa tông vàng chủ đạo mang ý nghĩa đại cát đại lợi cho ngày khai trương.'],
                ['Kệ lan hồ điệp Thịnh Vượng', 3200000, null, 0, 'Lan hồ điệp cao cấp nhiều cành, sang trọng — lời chúc thịnh vượng bền lâu.'], // mẫu hết hàng
                ['Kệ hoa Vạn Sự Như Ý', 1200000, null, 9, 'Kệ hoa lay ơn và hồng phối lá, chúc mọi việc hanh thông, vạn sự như ý.'],
                ['Kệ hướng dương Phú Quý', 1600000, null, 5, 'Hướng dương vươn cao rạng rỡ, chúc doanh nghiệp phát triển phú quý.'],
                ['Lẵng hoa Khai Trương Hồng Phát', 950000, 890000, 8, 'Lẵng hoa tươi nhiều tầng màu, phù hợp đặt tại quầy lễ tân, cửa hàng mới.'],
                ['Chậu kim tiền Tài Lộc', 650000, null, 10, 'Chậu cây kim tiền xanh mướt, dễ chăm, tượng trưng cho tiền tài và lộc lá.'],
                ['Kệ hoa đồng tiền Thành Công', 1350000, null, 6, 'Kệ hoa đồng tiền đỏ cam rực rỡ, gửi lời chúc thành công trọn vẹn.'],
            ],
            'hoa-chia-buon' => [
                ['Vòng hoa trắng Vĩnh Hằng', 1300000, null, 6, 'Vòng hoa trắng trang nghiêm, thay lời tiễn biệt và tưởng nhớ vĩnh hằng.'],
                ['Kệ hoa cúc trắng Tiễn Biệt', 1100000, null, 8, 'Kệ cúc trắng thanh khiết, bày tỏ lòng thành kính tiễn đưa người đã khuất.'],
                ['Kệ hoa lan trắng An Nhiên', 1600000, 1490000, 4, 'Lan trắng thanh cao, cầu chúc người đã khuất được an nhiên nơi cõi vĩnh hằng.'],
                ['Vòng hoa cúc vàng Tưởng Nhớ', 1200000, null, 5, 'Vòng cúc vàng trang trọng, gửi gắm niềm thương nhớ khôn nguôi.'],
                ['Kệ hoa ly trắng Thanh Thản', 1450000, null, 4, 'Hoa ly trắng thơm dịu, chia buồn cùng gia quyến trong lúc đau thương.'],
                ['Giỏ hoa chia buồn Bình Yên', 850000, null, 7, 'Giỏ hoa tông trắng xanh nhẹ nhàng, thay lời sẻ chia chân thành.'],
                ['Bó cúc trắng Thành Kính', 450000, null, 12, 'Bó cúc trắng giản dị, phù hợp viếng thăm, tưởng niệm.'],
            ],
            'hoa-de-ban' => [
                ['Bình hoa lily để bàn', 550000, null, 14, 'Bình lily thơm ngát, làm bừng sáng phòng khách hay bàn làm việc.'],
                ['Giỏ hoa hồng mini', 290000, 259000, 20, 'Giỏ hồng nhỏ xinh, vừa vặn cho bàn làm việc hoặc kệ sách.'],
                ['Chậu lan hồ điệp 3 cành', 1450000, null, 5, 'Chậu lan hồ điệp 3 cành nở bền 1–2 tháng, sang trọng cho không gian sống.'],
                ['Bình cẩm tú cầu xanh', 480000, null, 11, 'Cẩm tú cầu xanh mát mắt cắm bình thủy tinh, mang lại cảm giác thư thái.'],
                ['Hộp hoa sáp thơm', 250000, null, 30, 'Hoa sáp thơm giữ màu nhiều tháng, không cần chăm sóc — quà tặng tiện lợi.'],
                ['Bình tulip vàng', 420000, null, 13, 'Tulip vàng tươi cắm bình, đem sắc xuân vào mọi góc nhà.'],
                ['Bình cẩm chướng mix', 360000, null, 15, 'Cẩm chướng nhiều màu cắm bình, bền hoa, giá thân thiện.'],
                ['Chậu sen đá mini', 150000, null, 40, 'Chậu sen đá nhỏ xinh, dễ sống, hợp để bàn học, bàn làm việc.'],
                ['Bó oải hương khô', 320000, null, 18, 'Oải hương khô giữ hương thơm dịu nhẹ, trang trí và thư giãn.'],
                ['Bình thủy tiên trắng', 280000, null, 16, 'Thủy tiên trắng thanh tao, thơm nhẹ, biểu tượng của sự khởi đầu mới.'],
                ['Bình mẫu đơn trắng', 950000, 890000, 6, 'Mẫu đơn trắng bung cánh mềm mại, điểm nhấn sang trọng cho phòng khách.'],
            ],
            'hoa-cuoi' => [
                ['Hoa cầm tay cô dâu hồng trắng', 850000, null, 8, 'Hoa cầm tay phối hồng trắng và hồng phấn, cổ điển và thanh lịch.'],
                ['Hoa cầm tay baby trắng', 450000, null, 12, 'Hoa cầm tay baby trắng nhẹ tênh, hợp phong cách cưới tối giản.'],
                ['Hoa cầm tay cát tường', 650000, 590000, 9, 'Cát tường nhiều tầng cánh mềm mại, mang ý nghĩa may mắn trăm năm.'],
                ['Hoa cầm tay mẫu đơn hồng', 1350000, null, 4, 'Mẫu đơn hồng lộng lẫy dành cho cô dâu yêu vẻ đẹp kiêu sa.'],
                ['Hoa cài áo chú rể', 120000, null, 30, 'Hoa cài áo đồng bộ với hoa cầm tay cô dâu, tỉ mỉ từng chi tiết.'],
                ['Hoa cầm tay calla trắng', 980000, null, 6, 'Calla trắng thanh mảnh, sang trọng cho phong cách cưới hiện đại.'],
                ['Hoa cầm tay hướng dương Rustic', 590000, null, 7, 'Hướng dương phối lá khô phong cách rustic, rạng rỡ cho tiệc cưới ngoài trời.'],
                ['Cổng hoa cưới mini', 3500000, 3200000, 2, 'Cổng hoa tươi trang trí lối vào tiệc cưới, thiết kế theo tông màu yêu cầu.'],
                ['Hoa cổ tay phù dâu', 180000, null, 20, 'Hoa cổ tay xinh xắn dành cho phù dâu, đồng bộ màu sắc tiệc cưới.'],
            ],
        ];

        foreach ($products as $categorySlug => $items) {
            $category = Category::where('slug', $categorySlug)->firstOrFail();

            foreach ($items as [$name, $price, $salePrice, $stock, $description]) {
                Product::updateOrCreate(['slug' => Str::slug($name)], [
                    'category_id' => $category->id,
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'stock' => $stock,
                    'is_active' => true,
                ]);
            }
        }
    }
}
