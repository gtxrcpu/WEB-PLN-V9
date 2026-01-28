<?php

namespace PHPSTORM_META {

    override(\route(0), map([
        '' => '@',
    ]));

    override(\config(0), map([
        '' => '@',
    ]));

    override(\view(0), map([
        '' => '@',
    ]));

    expectedArguments(
        \route(),
        0,
        argumentsSet('routes')
    );

    expectedReturnValues(
        \route(),
        type(0)
    );
}
