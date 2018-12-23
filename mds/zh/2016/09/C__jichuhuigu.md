---

layout: post  
title: [C/C++] C++基础回顾  
subtitle:   
author: Hiko  
category: tech
tags: C/C++, 基础  
ctime: 2016-09-06 09:52:53  
lang: zh  

---

#### 0.0 类和结构

##### 0.1 struct 


	// C++ 还保留了 struct
	// 功能跟class类似,只是struct默认是pulic, class默认是private  
	
	typedef struct StuStruct {
	    int age;
	    char *name;

	    void info();
	} Stus;

	void Stus::info() {
	    printf("in info, name : %s, age : %d\n", this->name, this->age);
	}

##### 0.2 class

	class People {
	protected:
	    char *name;
	    int age;

	public:
	    People();

	    People(char *name, int age);
	};

	People::People() {
	    this->name = "def name";
	    this->age = 0;
	}

	People::People(char *name, int age) {
	    this->name = name;
	    this->age = age;
	}
	
	
##### 0.3 class 继承

	class Student : public People {
	protected:
	    int score;

	public:
	    Student();

	    Student(char *name, int age, int score);

	    void info();
	};

	Student::Student() {
	    this->score = 0;
	}
	
	// 冒号前面是派生类构造函数的头部，这和我们以前介绍的构造函数的形式一样，
	// 但它的形参列表包括了初始化基类和派生类的成员变量所需的数据；
	// 冒号后面是对基类构造函数的调用，这和普通构造函数的参数初始化表非常类似。
	Student::Student(char *name1, int age1, int score1) : 	People(name1, age1), score(score1) { }
	
	void Student::info() {
    	// age 可以不加this
    	cout << "name: " << this->name << " \n age :" << age << "\n score : " << this->score << endl;
	}
	
	int main() {
	    Student stu;
	    stu.info();

	    Student stu2("HikoQiu", 27, 99);
	    stu2.info();
	    Student::info();
	    std::string();
	    cout << "hello world" << endl;

	    return 0;
	}
	
实际上，对象(派生类赋值给父类)之间的赋值是成员变量的赋值，成员函数不存在赋值问题。在赋值时，会舍弃派生类自己的成员，也就是”大材小用“.

基类对象和派生类对象之间的赋值仅仅是对应的成员变量的赋值，不会影响成员函数，不会影响 this 指针。

成员变量和成员函数不在同一个内存区域，系统通过 this 指针来访问成员变量，但是却不通过它来访问成员函数。

系统通过对象的类型来访问成员函数。例如 p 的类型是 A，那么不管 p 指向哪个对象，都访问 A 类的成员函数。

##### 0.4 virtual class

	class A {
	protected:
	    int a;
	public:
	    A(int a) : a(a) { }
	};

	class B : virtual public A {
	protected:
	    int b;

	public:
	    B(int a, int b) : A(a), b(b) { }
	};

	class C : virtual public A {
	protected:
	    int c;

	public:
	    C(int a, int c) : A(a), c(c) { }
	};

	class D : virtual public B, virtual public C {
	protected:
	    int d;

	public:
	    // 现在，由于虚基类在派生类中只有一份成员变量，所以对这份成员变量的初始化必须由派生类直接给出。
	    // 如果不由最后的派生类直接对虚基类初始化，而由虚基类的直接派生类（如类B和类C）对虚基类初始化，
	    // 就有可能由于在类B和类C的构造函数中对虚基类给出不同的初始化参数而产生矛盾。
	    // 所以规定：在最后的派生类中不仅要负责对其直接基类进行初始化，还要负责对虚基类初始化。

	    // 可能有疑问：类D的构造函数通过初始化表调了虚基类的构造函数A，
	    // 而类B和类C的构造函数也通过初始化表调用了虚基类的构造函数A，
	    // 这样虚基类的构造函数岂非被调用了3次？大家不必过虑，C++编译系统只执行最后的派生类对虚基类的构造函数的调用，
	    // 而忽略虚基类的其他派生类（如类B和类C）对虚基类的构造函数的调用，这就保证了虚基类的数据成员不会被多次初始化。
	    D(int a, int b, int c, int d) : A(a), B(a, b), C(a, c), d(d) { }

	    // 没加virtual
	    // D(int a, int b, int c, int d) : B(a, b), C(a, c), d(d) { }

	    void display();
	};

	void D::display() {
	//    cout << "a:" << a << endl; // 如果没有使用virtual :  error: non-static member 'a' found in multiple base-class subobjects of type 'A':
	//    cout << "a:" << B::a << endl; // 如果没有使用virtual, 需要这样使用
	//    cout << this->B::a<<endl;

	    // 加了virtual 虚基类, 就不会由于多重继承 A 而导致 D 中有多个 a 字段, 所以不用像
	    // 上面的方式加类名和域解析符(B::a)
	    cout << "a : " << this->a << endl;
	    cout << "b : " << this->b << endl;
	    cout << "c : " << this->c << endl;
	    cout << "d : "  << this->d << endl;
	}

	int main() {

	    D d(1, 2, 3, 4);
	    d.display();

	    return 0;
	}
	
#### 1.0 重载

##### 1.1 操作符重载 (1)

	
	class complex {
	private:
	    double real, imag;
	public:
	    complex() : real(0.0), imag(0.0) { }

	    complex(double a, double b) : real(a), imag(b) { };

	    // 1.1 运算符重载 - 对象方法
	    // 返回值类型 operator 运算符名称 (形参表列){}
	    // 函数后的const表示这个函数不会修改所处class的member variable
	    //
	    // 备注: 即使没有定义 const 版本的重载函数，这段代码也是可以正确运行的，但是非 const 成员函数不能处理 const 对象，
	    // 所以在编程时通常会提供两个版本的运算符重载函数。
	    complex operator+(const complex &a) const;

	    // 2.1 运算符重载 - 全局方法
	    // 这里不能在方法之后设置const, 是因为该方法不是complex类的函数,而是友元函数.
	    friend complex operator*(const complex &a, const complex &b);

	    void info();
	};

	complex complex::operator+(const complex &a) const {
	    return complex(real + a.real, imag + a.imag);
	}

	void complex::info() {
	    cout << "real: " << real << " imag: " << imag << endl;
	}

	complex operator*(const complex &a, const complex &b) {
	    return complex(a.real * b.real, a.imag * b.imag);
	}


	int main() {

	    cout << "Hello operator overload." << endl;

	    complex c1(5.4, 6.8);
	    complex c2(9.9, 6.6);

	    complex c3 = c1 + c2;
	    complex c4 = c1 * c2;

	    c3.info();
	    c4.info();
	}
	
##### 1.2 操作符重载 (2)

	
	class Array {
	private:
	    int length;
	    int *num;

	public:
	    Array() : length(0), num(NULL) { }

	    Array(int n);

	    // 即使没有定义 const 版本的重载函数，这段代码也是可以正确运行的，但是非 const 成员函数不能处理 const 对象，所以在编程时通常会提供两个版本的运算符重载函数。

	    // 第一个函数最后不带 const，加上 const 意味着该成员函数是常成员函数，如果第一个函数后面也加上了const，则两个函数仅有返回值不同，编译器不能够区分这是函数重载，会报错。
	    // 这两个版本的重载函数其实很好理解，第一个能够修改对象，第二个只能访问对象而不能修改对象。
	    int &operator[](int i);

	    const int &operator[](int i) const;

	    int len();

	    void free();

	    ~Array();
	};

	Array::~Array() {
	    if (*num != NULL) {
	        delete[]num;
	    } else {
	        cout << "num is NULL." << endl;
	    }

	    cout << "~A() invoked." << endl;
	}

	Array::Array(int n) {
	    num = new int[n];
	    length = n;
	}

	int Array::len() {
	    return length;
	}

	void  Array::free() {
	    delete[] num;
	}

	int &Array::operator[](int i) {
	    if (i < 0 || i >= len()) {
	        throw string("\nout of bounds.");
	    }

	    return num[i];
	}

	const int &Array::operator[](int i) const {
	    if (i < 0 || i >= length) {
	        throw string("\nout of bounds.");
	    }

	    return num[i];
	}

	int main() {

	    Array A(5);

	    int i = 0;
	    try {
	        for (i = 0; i < A.len(); i++) {
	            A[i] = i;
	        }

	        for (i = 0; i < 6; i++) {
	            cout << "index i = " << i << ", val: " << A[i] << endl;
	        }
	    } catch (string s) {
	        cerr << s << ", i = " << i << endl;
	        A.free();
	    }

	    return 0;
	}

	/**
	 *
	 * 其他运算符:
	 *
	重载赋值运算符时，函数的参数和返回值类型都必须是对象的引用。以 Book 类为例来说，赋值运算符重载函数一般有两种原型：
	Book & operator=( Book &b );
	Book & operator=( const Book &b );
	返回值和参数都是 Book 类对象的引用。下面一种原型则规定在赋值时不能修改原来的对象。

	赋值运算符重载函数除了能有对象引用这样的参数之外，也能有其它参数。但是其它参数必须给出默认值。如下所示：
	Book & operator=(const Book &b, a = 10);
	 */

#### 2.0 模板

##### 2.1 模板 (1)

	
	class Array {
	private:
	    int length;
	    int *num;

	public:
	    Array() : length(0), num(NULL) { }

	    Array(int n);

	    // 即使没有定义 const 版本的重载函数，这段代码也是可以正确运行的，但是非 const 成员函数不能处理 const 对象，所以在编程时通常会提供两个版本的运算符重载函数。

	    // 第一个函数最后不带 const，加上 const 意味着该成员函数是常成员函数，如果第一个函数后面也加上了const，则两个函数仅有返回值不同，编译器不能够区分这是函数重载，会报错。
	    // 这两个版本的重载函数其实很好理解，第一个能够修改对象，第二个只能访问对象而不能修改对象。
	    int &operator[](int i);

	    const int &operator[](int i) const;

	    int len();

	    void free();

	    ~Array();
	};

	Array::~Array() {
	    if (*num != NULL) {
	        delete[]num;
	    } else {
	        cout << "num is NULL." << endl;
	    }

	    cout << "~A() invoked." << endl;
	}

	Array::Array(int n) {
	    num = new int[n];
	    length = n;
	}

	int Array::len() {
	    return length;
	}

	void  Array::free() {
	    delete[] num;
	}

	int &Array::operator[](int i) {
	    if (i < 0 || i >= len()) {
	        throw string("\nout of bounds.");
	    }

	    return num[i];
	}

	const int &Array::operator[](int i) const {
	    if (i < 0 || i >= length) {
	        throw string("\nout of bounds.");
	    }

	    return num[i];
	}

	int main() {

	    Array A(5);

	    int i = 0;
	    try {
	        for (i = 0; i < A.len(); i++) {
	            A[i] = i;
	        }

	        for (i = 0; i < 6; i++) {
	            cout << "index i = " << i << ", val: " << A[i] << endl;
	        }
	    } catch (string s) {
	        cerr << s << ", i = " << i << endl;
	        A.free();
	    }

	    return 0;
	}

	/**
	 * 其他运算符:
	重载赋值运算符时，函数的参数和返回值类型都必须是对象的引用。以 Book 类为例来说，赋值运算符重载函数一般有两种原型：
	Book & operator=( Book &b );
	Book & operator=( const Book &b );
	返回值和参数都是 Book 类对象的引用。下面一种原型则规定在赋值时不能修改原来的对象。

	赋值运算符重载函数除了能有对象引用这样的参数之外，也能有其它参数。但是其它参数必须给出默认值。如下所示：
	Book & operator=(const Book &b, a = 10);
	 */
