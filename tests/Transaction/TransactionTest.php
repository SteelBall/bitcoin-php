<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Transaction;

use BitWasp\Bitcoin\Script\Script;
use BitWasp\Bitcoin\Tests\AbstractTestCase;
use BitWasp\Bitcoin\Transaction\Factory\TxBuilder;
use BitWasp\Bitcoin\Transaction\Transaction;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Transaction\TransactionInterface;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use BitWasp\Buffertools\Buffer;

class TransactionTest extends AbstractTestCase
{

    public function testGetValueOut()
    {
        $value = 10;
        $count = 5;

        $outputs = [];
        for ($i = 0; $i < $count; $i++) {
            $outputs[] = new TransactionOutput($value, new Script());
        }

        $tx = new Transaction(1, [], $outputs);
        $this->assertEquals($count * $value, $tx->getValueOut());
    }

    public function testGetVersionEmpty()
    {
        $tx = new Transaction();
        $this->assertEquals(TransactionInterface::DEFAULT_VERSION, $tx->getVersion());
    }

    /**
     * @depends testGetVersionEmpty
     */
    public function testSetVersion()
    {
        $tx = new Transaction(191);
        $this->assertSame(191, $tx->getVersion());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Transaction version is outside valid range
     */
    public function testSetVersionException()
    {
        new Transaction(4294967999);
    }

    public function testGetLockTime()
    {
        // Default
        $tx = new Transaction();
        $this->assertEquals(0, $tx->getLockTime());
    }

    public function testSetLockTime()
    {
        $lockTime = 1093;
        $tx = new Transaction(1, [], [], [], $lockTime);

        $this->assertEquals($lockTime, $tx->getLockTime());
    }

    /**
     * @expectedException \Exception
     */
    public function testSetLockTimeException()
    {
        new Transaction(1, [], [], [], 4294967297);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetInputException()
    {
        $tx = new Transaction();
        $tx->getInput(0);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetOutputException()
    {
        $tx = new Transaction();
        $tx->getOutput(0);
    }

    public function testFromHex_version3_type1()
    {
        $hex = '030001000126d3cb36b5360a23f5f4a2ea4c98d385c0c7a80788439f52a237717d799356a6000000006b483045022100b025cd823cf6b746e97a1e5657c1c6f150bc63530734b1c5dacef2cfad53a8ea022073d0801e18a082eaee70838f2cfc19c78b88b879af7d3e42023d61852ad289e701210222865251150a58f0f89602cb812046cc38c84d67e3dc74edb9061aaed19c2bdefeffffff0143c94fbb000000001976a9145cbfea4a74cfeb5f801f2cbaf38a9bac7ebebb0e88ac00000000fd120101000000000026d3cb36b5360a23f5f4a2ea4c98d385c0c7a80788439f52a237717d799356a60100000000000000000000000000ffffc38d008f4e1f8a94fb062049b841f716dcded8257a3632fb053c8273ec203d1ea62cbdb54e10618329e4ed93e99bc9c5ab2f4cb0055ad281f9ad0808a1dda6aedf12c41c53142828879b8a94fb062049b841f716dcded8257a3632fb053c00001976a914e4876df5735eaa10a761dca8d62a7a275349022188acbc1055e0331ea0ea63caf80e0a7f417e50df6469a97db1f4f1d81990316a5e0b412045323bca7defef188065a6b30fb3057e4978b4f914e4e8cc0324098ae60ff825693095b927cd9707fe10edbf8ef901fcbc63eb9a0e7cd6fed39d50a8cde1cdb4';
        $extra_payload = '01000000000026d3cb36b5360a23f5f4a2ea4c98d385c0c7a80788439f52a237717d799356a60100000000000000000000000000ffffc38d008f4e1f8a94fb062049b841f716dcded8257a3632fb053c8273ec203d1ea62cbdb54e10618329e4ed93e99bc9c5ab2f4cb0055ad281f9ad0808a1dda6aedf12c41c53142828879b8a94fb062049b841f716dcded8257a3632fb053c00001976a914e4876df5735eaa10a761dca8d62a7a275349022188acbc1055e0331ea0ea63caf80e0a7f417e50df6469a97db1f4f1d81990316a5e0b412045323bca7defef188065a6b30fb3057e4978b4f914e4e8cc0324098ae60ff825693095b927cd9707fe10edbf8ef901fcbc63eb9a0e7cd6fed39d50a8cde1cdb4';

        $tx = TransactionFactory::fromHex($hex);

        $this->assertInstanceOf(Transaction::class, $tx);
        $this->assertEquals(3, $tx->getVersion());
        $this->assertEquals(0, $tx->getLockTime());
        $this->assertEquals(1, count($tx->getInputs()));
        $this->assertEquals(1, count($tx->getOutputs()));
        $this->assertEquals(1, $tx->getType());
        $this->assertEquals(274, $tx->getExtraPayload()->getSize());
        $this->assertEquals($extra_payload, $tx->getExtraPayload()->getHex());

        $serialized = $tx->getBuffer()->getHex();
        $this->assertSame($hex, $serialized);

        $this->assertEquals('62330c04f20acc541c8d4f3022ba2b032ea5530c476e61dc9c4235ac20d10f4f', $tx->getTxId()->getHex());
    }

    public function testFromHex()
    {
        $hex = '0100000014e3b8f4a75dd3a033744d8245148d5a8b734e6ebb157ac12d49e65d4f01f6c86c000000006c493046022100e8a2df24fd890121d8dd85c249b742d0585ec17d18b1bf97050e72eaaceb1580022100d7c37967048a617d7551c8249ea5e58cbf71a0508a6c459dd3a9dfefba3c592f0121030c51892ad8c9df7590c84bc2475576d6dc0815a5bf3ca37f1c58fe82e45d9ef5ffffffff0f1408fa2773334487d1d37e45cb399d049c7e46db3faadfcc204656bce57f5e000000006b483045022100cc339da0e9330b2375124a5fae678b130a4e5215310d85a1db2c7da32dd9633a02205fb02c932eab91733920bab341ad61097f2ff5dc73e46577ce3b70fd0e2ecc4b0121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff72a329f6d5cb92a3cd9e8aa252f0c683f28cb57e8884cfff42ffdddaca86f2e4000000006a473044022013f6b4e159ba9f88825746f9c7d1131cb14667a83d6b3871b5bab2a8f9f75759022024e29aa4bb7c7b994468c0a7a7add2bb180fc9a48b0187d2e06239b358cce8eb0121037cf462b312b1696f2654a21100a6a726238b91455d0f50f69526335d9022fdc5ffffffff49d1bb27b1027249326f3ad35a06b3c7fc9af2c0318caa02c1a90ae2e2a46cf8000000006c493046022100b6381612d4a5b1c75d57fdf93ce8fe39d4541a97af5cd312dbd91925cbd2037e022100a32954780c7d711059524f03ed78cf2e234554977a7e2139e7e7c6949835da660121025a4098ac03d3f706bfdaa795f80350aad15b5a6a578cee2991c16edae0255e76ffffffffc6b749366d13fca59f264b2714bc090660eb291361f23d79b6515f48f35dfd2c010000006b48304502205421f94ee54d829f921785860d4603b82caf8f3722f768e26970f4baa625c0dc022100f3c0cee26b1a98558386fbfcb568100582acc7bc8423e3402313298e0a3291520121026bff9f45e1645a6a67f70e80439d982d3d6dd0fe31258f93ae65161a14b54648ffffffff1f48a79c65634eb19c4a7eaca76a3f8f7c47b649cf54c8fbb5be6f87f6a42fa7010000006a47304402207907ec39e5ff6a85c5c0e5b8d7a81750e1e450e85839d87cdde4cfba405f6ee602205439aa642218bb676d2d2b9c0c63dd44395ad85fad2954ac1794bb4b35e134fc0121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff3a339577e45201a85797937aa44fe7a31f9240ba8a9169e46c32bbd82fbc2ae3000000006a473044022018e0dbfb5ee617fb7d1c28c672fb5428cbc1edb6ed8cc76bc68682432c4bfe450220187a7d4e7b2af69a8e7c7ebfd1b6f02143cf7a32c1041c01bc5324bcd97101880121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff7d0e4f252c644d051e35ee839abde736f26dd2046c7ca18711c759e3c86fb15a220000006a473044022004dfb719c5afca95db100f4e55fb4dd27f4f9faf1a6eff390d9bbdbf9b28fa4b02206dfd0d5f74b4c6d8424d44461c6a230fc174902d2be8d1792e029c3d67a1be9601210388da4b2db35387cf3bfc786453e8ca952b5386c95ce1b39e7ff5cf62cb7033fbfffffffff009c6f8a9b86d97989eaaacf3ada9f9428bae3a1a01339b85fa541680271c3a000000006b48304502203c17278401d3f7e6ab56597b6b88c783db5aa534897954e92a95732b5d714a860221008be28d72988a8ba69ff3889337468df0e83612e9cfbead9118fb8f4decfb3e93012102cda10f1505ff6a3613f6aa31becba07e44eddc25110ae083e360577556f0178dffffffff75068f094fd516d1658dac8c88ae027852a84423831b780925fb3d591a308227010000006b48304502203ce1a111748534bc601cc4a879f4097b098f7622d28a3da89ac7b90f3b29ee52022100b7de6a03241f6fea37179c3b2f68f45b69c963edc19dc0b3b0350bb8ffbd9063012102c64a6d411eef9f79890d317ad72329608be5f58f6757e1c5c6d4f21076345ddcfffffffffc844c99e1adab4677dda6b5def33ce549293a40d08da6a8c36fb9274c76ba43000000006c493046022100ee2b946560aced0633a5151f1fa5dd0249d68bef420e43e1f7edfd0e83619cfd022100a433121fb022efb69043310bd982a842e7f5be737069d3535962ecd78c4954070121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff32f65f550d4ef09e451303af1d2f9964b4f62e7b6562d5dbe23d4be89ae32e36010000006b483045022100bb4b1af8c22e51e9a3f71fb2cd883746e3812cbfd6a0695bbd22e53d573cb6f20220539e478519411e1d277a844810ab756004cc7755ff585cda7aa2c9d93ab4570b01210290a17b828f33417e7e3cb179bd50a621c8381964e6e239e91eacf3f414018770fffffffff6a6dafe0ae32f6c891de86c4aa12b318eeb89ca4d8792906ae03fd0db04c76f010000006b48304502203dfe2f1d58fdcab2f67f079c14a26c69e88718d38615d5de8230ee74c1e55d9e022100bc7651cf77f142366b502c65c59790a3d2cfa52852feab04212d3ea68e040be0012103214083806ce8aebf35544845c73f1e9d4dcad2fc6057e6cda963498f5420fef9ffffffff11c3e0e2a520bd829fab9b4311e603de79ce1304e1f28204334f79d1fd2c9138010000006b48304502206196ca600a02bf7fd210489e520b915b521dfa84a37bf56f2022aa8e89d62e6f022100949aabaafe92aaa8e207f0699a4745179fdc34a475d5c783785a58744ed5dd410121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff54dffe807c0b935528100313322949738717dc2af2f374f2a72af24b82291f70000000006b483045022100b84abff6f636082d2b85da4de25de13388a7901f0df5b87d871305e694667b4f0220477619c2140bc2d7dfab0d2b7d49f0729afb951831c7a64ee4bede7d8a46960b012102cda10f1505ff6a3613f6aa31becba07e44eddc25110ae083e360577556f0178dffffffff9923148b409a0888e42612e964216eb575b8fe4d0cfe5aad84323962c219373b000000006b483045022100cc88b67cce1655c38c60ada140b9c34343411cc309c45410edf2b4f133520c03022044c3554a294f6a412219b710689d67c5d8519fc6a139ee311985a14b8b55fd6f012102cda10f1505ff6a3613f6aa31becba07e44eddc25110ae083e360577556f0178dffffffff5bb09bbcbdd756af1f08457f7c1319aa67d5488f359b58e6f36920b808c789bb010000006a473044022051b255884c8f118780e394c054c5df12d813e3b9983a9854c89e7ffd015cbf5702200ed58fac38a91e19d755d30d910a04aa29848608b72f99aef68e0ecedcbdca53012103f57996ef25762717a75b6d75f0166a33f775783766513118b5476933d7af8078ffffffffc3c978e2e413506b75b7882cc38cf6f40d199c7226f73b5e4ab8295703fd4b03d50000006b48304502201a07c4ce0f76c4f5d96c45811bfa08c00483c987e1297324f40136a4c452c306022100d553873b6b3c20ae2b6a588704bae9daa5093b17283e0b196a27d6e4022ff8a50121032beebbe7e386f1fd27a9a3e59640deaa5f60835ab789012175c0f517149c77e3ffffffff3944343f32f93d43752bea7d916571e236295fbff53c7a9133d09f741116fac5000000006b483045022074aac638f77fed744feb1e99f4a71f20278686fc2f91264058facd9983cde9fb0221008250b8d62dbb8d3954517f9f80e236d1c74720723459b5d69656dadf781ee2e9012102cda10f1505ff6a3613f6aa31becba07e44eddc25110ae083e360577556f0178dffffffff781119976951dfc6f8a617077068fcb623475acef7eff09808a5ef3281a0ef70010000006b48304502200236e5c6b0787756449043fd413307c176be2b39fb7da11a18e10708a72877fe022100836b99f32532039e6326cbf282cbf78140c53883c474443bd6e6145e52dfb08d012103dd033367f07aee4f365a47cdf3c45147887492c6d20788970296d3b94eb1cfd8ffffffff1365487900000000001976a9143f6cd41f7caeda87b86bc9e009295f179612f45488acc7440f00000000001976a914df90390bee06889ed003b3c4d024a9fd211cf96788ace0ee4504000000001976a9141d8aa01e6628f333fd6fd9d3b88c3f761869caa488acd5080200000000001976a91412d24a8a61e1cd37825fd31bd61eebc2c32ad77688ac0842b582000000001976a9147fb7cccd54bf322ddae63b6b2e20a3624622091888acd5113916000000001976a914e75fa783b5b319578a53f2f18c37d88513d060b088ac80969800000000001976a9140ce8c479dad1b78baeea2217af17655b908b59b888ace8fb1204000000001976a9141a80a0ee9b4f1d03c3fb2220e4124d441431d41888ac00e1f505000000001976a914b1fdf35dd37a1a5359ad829f5f43760cd1ea61d588ac80626a94000000001976a9142a929cae46f0b4e5742ae38d6040f11a2b70e7d188ac109d0a02000000001976a914a4b5bfbe26ef9ac8d6e06738b50065b25f3dcce288acab110400000000001976a9146fbc4dd98c194853dc9a3e6f195bddad8eef609788ac00e1f505000000001976a914e1733c6b9e4c98f4ddc19370dd5cae0727af04e788ac5d44d502000000001976a9147514615f455bdfb8b9da74c7707ef0426606c33288ac404b4c00000000001976a914166e78015832ba760593bb292993391d4554a39a88acf0190b01000000001976a91409859ea62e00e0531873c135e37efbeca610d36788ace2fcae8f000000001976a914b40d92da29c478049a8d15bfd85b6ce230863df288acc07a3e22000000001976a9141e12d7845e76857bfba94079c1faca68b3ae24c088ac9e130e01000000001976a9144bdb0b7712726c2684461cd4e5b08934def0187c88ac00000000';
        $tx = TransactionFactory::fromHex($hex);
        $this->assertInstanceOf(Transaction::class, $tx);
        $this->assertEquals(1, $tx->getVersion());
        $this->assertEquals(0, $tx->getLockTime());
        $this->assertEquals(20, count($tx->getInputs()));
        $this->assertEquals(19, count($tx->getOutputs()));

        $serialized = $tx->getBuffer()->getHex();
        $this->assertSame($hex, $serialized);

        $this->assertEquals('048254a713f83b2deaecc781c81959fa8816d47138c7eead06c162def42a3236', $tx->getTxId()->getHex());
    }

    public function testFromHex2()
    {
        $hex = '0100000001c9a2d5381a059bbab241524d69e28b76c8083c5f46000d268ee2e52686061d5d01000000fdfe0000493046022100d231e144ad82f009eae982df1e44f407dad98aac614e970565bd632cd081388e022100d3c138566c2f67162f8f250b26ac928b104272a5efee75818a48d7004da7d18b0147304402201e4106936aaf8e2582402d59f95cb3fa69a72fd812042cce7a54c5cc81dd7e9c022079dac8f738c676abbf84f1ad21c215b7782296bdf45f1a41a2c69b52e3039d51014c695221024882ca54cd89c1f14aea2c843fa0109f6339bd4df166a12454d195fefb9e84922102e04b69fe7139498cd99ae410f07d781900357f0b3b1ccaf997b2c9b1e7c185a82103ea5042dd903e5d717682ec9a5071f1516bf2cd6096c31f49b0d6c25ad9326ad853aeffffffff02a0e7be040000000017a9143409969e2d14b4694eb1188028e615353740273c87a08601000000000017a9141d6fcb721f40cac5b0f53f7f4924f4f20aee9cb78700000000';
        $tx = TransactionFactory::fromHex($hex);
        $this->assertInstanceOf(Transaction::class, $tx);
        $this->assertEquals(1, $tx->getVersion());
        $this->assertEquals(0, $tx->getLockTime());
        $this->assertEquals(1, count($tx->getInputs()));
        $this->assertEquals(2, count($tx->getOutputs()));

        $serialized = $tx->getBuffer()->getHex();

        $this->assertSame($hex, $serialized);

        $this->assertEquals('a114b696661d740838d9cf811602efff7aad2abf087506830b37d5f2e43bc72d', $tx->getTxId()->getHex());
    }

    public function testSerialize()
    {
        $hex = '0100000014e3b8f4a75dd3a033744d8245148d5a8b734e6ebb157ac12d49e65d4f01f6c86c000000006c493046022100e8a2df24fd890121d8dd85c249b742d0585ec17d18b1bf97050e72eaaceb1580022100d7c37967048a617d7551c8249ea5e58cbf71a0508a6c459dd3a9dfefba3c592f0121030c51892ad8c9df7590c84bc2475576d6dc0815a5bf3ca37f1c58fe82e45d9ef5ffffffff0f1408fa2773334487d1d37e45cb399d049c7e46db3faadfcc204656bce57f5e000000006b483045022100cc339da0e9330b2375124a5fae678b130a4e5215310d85a1db2c7da32dd9633a02205fb02c932eab91733920bab341ad61097f2ff5dc73e46577ce3b70fd0e2ecc4b0121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff72a329f6d5cb92a3cd9e8aa252f0c683f28cb57e8884cfff42ffdddaca86f2e4000000006a473044022013f6b4e159ba9f88825746f9c7d1131cb14667a83d6b3871b5bab2a8f9f75759022024e29aa4bb7c7b994468c0a7a7add2bb180fc9a48b0187d2e06239b358cce8eb0121037cf462b312b1696f2654a21100a6a726238b91455d0f50f69526335d9022fdc5ffffffff49d1bb27b1027249326f3ad35a06b3c7fc9af2c0318caa02c1a90ae2e2a46cf8000000006c493046022100b6381612d4a5b1c75d57fdf93ce8fe39d4541a97af5cd312dbd91925cbd2037e022100a32954780c7d711059524f03ed78cf2e234554977a7e2139e7e7c6949835da660121025a4098ac03d3f706bfdaa795f80350aad15b5a6a578cee2991c16edae0255e76ffffffffc6b749366d13fca59f264b2714bc090660eb291361f23d79b6515f48f35dfd2c010000006b48304502205421f94ee54d829f921785860d4603b82caf8f3722f768e26970f4baa625c0dc022100f3c0cee26b1a98558386fbfcb568100582acc7bc8423e3402313298e0a3291520121026bff9f45e1645a6a67f70e80439d982d3d6dd0fe31258f93ae65161a14b54648ffffffff1f48a79c65634eb19c4a7eaca76a3f8f7c47b649cf54c8fbb5be6f87f6a42fa7010000006a47304402207907ec39e5ff6a85c5c0e5b8d7a81750e1e450e85839d87cdde4cfba405f6ee602205439aa642218bb676d2d2b9c0c63dd44395ad85fad2954ac1794bb4b35e134fc0121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff3a339577e45201a85797937aa44fe7a31f9240ba8a9169e46c32bbd82fbc2ae3000000006a473044022018e0dbfb5ee617fb7d1c28c672fb5428cbc1edb6ed8cc76bc68682432c4bfe450220187a7d4e7b2af69a8e7c7ebfd1b6f02143cf7a32c1041c01bc5324bcd97101880121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff7d0e4f252c644d051e35ee839abde736f26dd2046c7ca18711c759e3c86fb15a220000006a473044022004dfb719c5afca95db100f4e55fb4dd27f4f9faf1a6eff390d9bbdbf9b28fa4b02206dfd0d5f74b4c6d8424d44461c6a230fc174902d2be8d1792e029c3d67a1be9601210388da4b2db35387cf3bfc786453e8ca952b5386c95ce1b39e7ff5cf62cb7033fbfffffffff009c6f8a9b86d97989eaaacf3ada9f9428bae3a1a01339b85fa541680271c3a000000006b48304502203c17278401d3f7e6ab56597b6b88c783db5aa534897954e92a95732b5d714a860221008be28d72988a8ba69ff3889337468df0e83612e9cfbead9118fb8f4decfb3e93012102cda10f1505ff6a3613f6aa31becba07e44eddc25110ae083e360577556f0178dffffffff75068f094fd516d1658dac8c88ae027852a84423831b780925fb3d591a308227010000006b48304502203ce1a111748534bc601cc4a879f4097b098f7622d28a3da89ac7b90f3b29ee52022100b7de6a03241f6fea37179c3b2f68f45b69c963edc19dc0b3b0350bb8ffbd9063012102c64a6d411eef9f79890d317ad72329608be5f58f6757e1c5c6d4f21076345ddcfffffffffc844c99e1adab4677dda6b5def33ce549293a40d08da6a8c36fb9274c76ba43000000006c493046022100ee2b946560aced0633a5151f1fa5dd0249d68bef420e43e1f7edfd0e83619cfd022100a433121fb022efb69043310bd982a842e7f5be737069d3535962ecd78c4954070121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff32f65f550d4ef09e451303af1d2f9964b4f62e7b6562d5dbe23d4be89ae32e36010000006b483045022100bb4b1af8c22e51e9a3f71fb2cd883746e3812cbfd6a0695bbd22e53d573cb6f20220539e478519411e1d277a844810ab756004cc7755ff585cda7aa2c9d93ab4570b01210290a17b828f33417e7e3cb179bd50a621c8381964e6e239e91eacf3f414018770fffffffff6a6dafe0ae32f6c891de86c4aa12b318eeb89ca4d8792906ae03fd0db04c76f010000006b48304502203dfe2f1d58fdcab2f67f079c14a26c69e88718d38615d5de8230ee74c1e55d9e022100bc7651cf77f142366b502c65c59790a3d2cfa52852feab04212d3ea68e040be0012103214083806ce8aebf35544845c73f1e9d4dcad2fc6057e6cda963498f5420fef9ffffffff11c3e0e2a520bd829fab9b4311e603de79ce1304e1f28204334f79d1fd2c9138010000006b48304502206196ca600a02bf7fd210489e520b915b521dfa84a37bf56f2022aa8e89d62e6f022100949aabaafe92aaa8e207f0699a4745179fdc34a475d5c783785a58744ed5dd410121033fd9e31bd2bdc7029d6f1cca55655c4b484aca7fdea11547b37a4aeaf347e132ffffffff54dffe807c0b935528100313322949738717dc2af2f374f2a72af24b82291f70000000006b483045022100b84abff6f636082d2b85da4de25de13388a7901f0df5b87d871305e694667b4f0220477619c2140bc2d7dfab0d2b7d49f0729afb951831c7a64ee4bede7d8a46960b012102cda10f1505ff6a3613f6aa31becba07e44eddc25110ae083e360577556f0178dffffffff9923148b409a0888e42612e964216eb575b8fe4d0cfe5aad84323962c219373b000000006b483045022100cc88b67cce1655c38c60ada140b9c34343411cc309c45410edf2b4f133520c03022044c3554a294f6a412219b710689d67c5d8519fc6a139ee311985a14b8b55fd6f012102cda10f1505ff6a3613f6aa31becba07e44eddc25110ae083e360577556f0178dffffffff5bb09bbcbdd756af1f08457f7c1319aa67d5488f359b58e6f36920b808c789bb010000006a473044022051b255884c8f118780e394c054c5df12d813e3b9983a9854c89e7ffd015cbf5702200ed58fac38a91e19d755d30d910a04aa29848608b72f99aef68e0ecedcbdca53012103f57996ef25762717a75b6d75f0166a33f775783766513118b5476933d7af8078ffffffffc3c978e2e413506b75b7882cc38cf6f40d199c7226f73b5e4ab8295703fd4b03d50000006b48304502201a07c4ce0f76c4f5d96c45811bfa08c00483c987e1297324f40136a4c452c306022100d553873b6b3c20ae2b6a588704bae9daa5093b17283e0b196a27d6e4022ff8a50121032beebbe7e386f1fd27a9a3e59640deaa5f60835ab789012175c0f517149c77e3ffffffff3944343f32f93d43752bea7d916571e236295fbff53c7a9133d09f741116fac5000000006b483045022074aac638f77fed744feb1e99f4a71f20278686fc2f91264058facd9983cde9fb0221008250b8d62dbb8d3954517f9f80e236d1c74720723459b5d69656dadf781ee2e9012102cda10f1505ff6a3613f6aa31becba07e44eddc25110ae083e360577556f0178dffffffff781119976951dfc6f8a617077068fcb623475acef7eff09808a5ef3281a0ef70010000006b48304502200236e5c6b0787756449043fd413307c176be2b39fb7da11a18e10708a72877fe022100836b99f32532039e6326cbf282cbf78140c53883c474443bd6e6145e52dfb08d012103dd033367f07aee4f365a47cdf3c45147887492c6d20788970296d3b94eb1cfd8ffffffff1365487900000000001976a9143f6cd41f7caeda87b86bc9e009295f179612f45488acc7440f00000000001976a914df90390bee06889ed003b3c4d024a9fd211cf96788ace0ee4504000000001976a9141d8aa01e6628f333fd6fd9d3b88c3f761869caa488acd5080200000000001976a91412d24a8a61e1cd37825fd31bd61eebc2c32ad77688ac0842b582000000001976a9147fb7cccd54bf322ddae63b6b2e20a3624622091888acd5113916000000001976a914e75fa783b5b319578a53f2f18c37d88513d060b088ac80969800000000001976a9140ce8c479dad1b78baeea2217af17655b908b59b888ace8fb1204000000001976a9141a80a0ee9b4f1d03c3fb2220e4124d441431d41888ac00e1f505000000001976a914b1fdf35dd37a1a5359ad829f5f43760cd1ea61d588ac80626a94000000001976a9142a929cae46f0b4e5742ae38d6040f11a2b70e7d188ac109d0a02000000001976a914a4b5bfbe26ef9ac8d6e06738b50065b25f3dcce288acab110400000000001976a9146fbc4dd98c194853dc9a3e6f195bddad8eef609788ac00e1f505000000001976a914e1733c6b9e4c98f4ddc19370dd5cae0727af04e788ac5d44d502000000001976a9147514615f455bdfb8b9da74c7707ef0426606c33288ac404b4c00000000001976a914166e78015832ba760593bb292993391d4554a39a88acf0190b01000000001976a91409859ea62e00e0531873c135e37efbeca610d36788ace2fcae8f000000001976a914b40d92da29c478049a8d15bfd85b6ce230863df288acc07a3e22000000001976a9141e12d7845e76857bfba94079c1faca68b3ae24c088ac9e130e01000000001976a9144bdb0b7712726c2684461cd4e5b08934def0187c88ac00000000';
        $tx = TransactionFactory::fromHex($hex);
        $this->assertEquals($hex, $tx->getBuffer()->getHex());
    }

    public function testCoinbaseTx()
    {
        $txId = 'fcbe95cd172371d9c35569fbb441774d0fa1adcc2426eff500a4c00a4eb2b6c4';
        $raw = '01000000010000000000000000000000000000000000000000000000000000000000000000ffffffff2703681e05062f503253482f048dcc9854087400023054c704000d4254434368696e6120506f6f6c0000000001ff702896000000001976a9142c30a6aaac6d96687291475d7d52f4b469f665a688ac00000000';

        $tx = TransactionFactory::fromHex($raw);

        $this->assertTrue($tx->getInput(0)->isCoinBase());
        $this->assertEquals(0, $tx->getInput(0)->getSequence());
        $this->assertEquals('03681e05062f503253482f048dcc9854087400023054c704000d4254434368696e6120506f6f6c', $tx->getInput(0)->getScript()->getHex());

        $this->assertEquals($raw, $tx->getHex());
        $this->assertEquals($txId, $tx->getTxId()->getHex());
        $this->assertTrue($tx->isCoinbase());
    }

    public function testEquals()
    {
        $builder = (new TxBuilder())
            ->version(1)
            ->input('abcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcd', 0)
            ->output(1, new Script(new Buffer('a')));

        $tx = $builder->get();
        $txEq = $builder->get();
        $txBadLock = clone $builder->locktime(123)->get();
        $txBadVer = clone $builder->version(123)->get();

        $this->assertTrue($tx->equals($txEq));
        $this->assertFalse($tx->equals($txBadLock));
        $this->assertFalse($tx->equals($txBadVer));

        $this->assertFalse($tx->equals((new TxBuilder())
            ->version(1)
            ->input('abcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcd', 0)
            ->input('123dabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcd', 0)
            ->output(1, new Script(new Buffer('a')))
            ->get()));

        $this->assertFalse($tx->equals((new TxBuilder())
            ->version(1)
            ->input('abcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcd', 0)
            ->output(1, new Script(new Buffer('a')))
            ->output(21, new Script(new Buffer('b')))
            ->get()));

        $this->assertFalse($tx->equals((new TxBuilder())
            ->version(1)
            ->input('abcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcd', 0)
            ->output(1123, new Script(new Buffer('abdffd')))
            ->get()));

        $this->assertFalse($tx->equals((new TxBuilder())
            ->version(1)
            ->input('9999abcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcd', 0)
            ->output(1, new Script(new Buffer('a')))
            ->get()));
    }

    public function testOpReturnTx()
    {
        $txId = '9b831ef60919c42be1ede10fbe4c773a622144669f7dbaa7bb4452574a9263a2';
        $raw = '0100000001aadf4be6d94e6028986a70432e97051174dc7ee0b7d0fd4241871a6f5a4c8978000000008a4730440220730f9b2fdd4e94cf0ead16767151e08fc178ae05cea0528583a993988b4360f3022010cbecd765dad96ba86f47df7f770914e6e07d12756b1c7ccc2d3262b68ecaf501410441b5e5365075fc3a3df8313abefdceb0a7f67f5253f96f7ea2cb5d952ac6537adad5e25ca9eef68a486c3c0fe4c87e9fe566b1849c9da03b02c686dfecee99c9ffffffff03f8380900000000001976a91463241d31675c1d761c734649cb3681e92bdf86ee88ac00000000000000000c6a0a6f6d000000468000002a22020000000000001976a91463241d2ef3fcfe30e496135d66c16e66f87b6a4788ac00000000';

        $tx = TransactionFactory::fromHex($raw);

        $this->assertEquals(3, count($tx->getOutputs()));
        $this->assertEquals('6a0a6f6d000000468000002a', $tx->getOutput(1)->getScript()->getHex());

        $this->assertEquals($raw, $tx->getHex());
        $this->assertEquals($txId, $tx->getTxId()->getHex());
    }
}
